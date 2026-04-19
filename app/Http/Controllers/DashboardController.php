<?php

namespace App\Http\Controllers;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\TechnicalRequest;



class DashboardController extends Controller
{    

    public function login(){  
      
        return view('backoffice.dashboard.index');
    }  

    public function index()
    {  
        $user = auth()->user();

        if ($user && $user->hasRole('user')) {
            return redirect()->route('backoffice.technical_requests.index');
        }

        if (!app()->getLocale()) {
            app()->setLocale('pt');        
        }

        // Gráfico de utilizadores por mês
        $users = User::select(DB::raw("COUNT(*) as count"), DB::raw("MONTHNAME(created_at) as month_name"))
                    ->orderBy(DB::raw("Month(created_at)"))
                    ->groupBy(DB::raw("Month(created_at)"), "month_name")
                    ->pluck('count', 'month_name');

        $labels = $users->keys();
        $data = $users->values();

        $hoje = Carbon::today();

        // Pedidos operacionais da Hotline
        $pedidosPendentes = TechnicalRequest::whereIn('estado', ['pendente', 'agendado'])
            ->with('store')
            ->orderByDesc('data_pedido')
            ->get();
        $totalPedidosPendentes = TechnicalRequest::where('estado', 'pendente')->count();
        $totalPedidosAgendados = TechnicalRequest::where('estado', 'agendado')->count();
        $totalPedidosPorAtribuir = TechnicalRequest::whereNull('assigned_technician_id')
            ->whereNotIn('estado', ['concluido', 'cancelado'])
            ->count();
        $totalAguardaPeca = TechnicalRequest::where('estado', 'aguarda_peca')->count();
        $totalConcluidosHoje = TechnicalRequest::where('estado', 'concluido')
            ->whereDate('data_resolucao', $hoje)
            ->count();
        $totalConcluidosSemana = TechnicalRequest::where('estado', 'concluido')
            ->whereBetween('data_resolucao', [$hoje->copy()->startOfWeek(), $hoje->copy()->endOfWeek()])
            ->count();
        $pedidosPrioritarios = TechnicalRequest::whereIn('estado', ['pendente', 'agendado', 'aguarda_peca'])
            ->with(['store', 'assignedTechnician'])
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderByRaw("FIELD(estado, 'pendente', 'aguarda_peca', 'agendado')")
            ->orderBy('data_pedido')
            ->limit(8)
            ->get();

        // Próximos agendamentos do usuário logado (próximos 7 dias)
        $user = auth()->user();
        $hoje = Carbon::today();
        $proximosAgendamentos = collect();
        $eventosCalendario = [];
        if ($user) {
            $proximosAgendamentos = $user->taskSchedules()
                ->where(function($q) use ($hoje) {
                    $q->where(function($w) use ($hoje) {
                        $w->whereNull('repetir')
                          ->orWhere('repetir', 0);
                    })->whereDate('data_limite', '>=', $hoje)
                    ->orWhere(function($w) use ($hoje) {
                        $w->where('repetir', 1)
                          ->whereNotNull('final_date')
                          ->whereDate('final_date', '>=', $hoje);
                    });
                })
                ->with(['task', 'users' => function($uq) use ($user) {
                    $uq->where('users.id', $user->id);
                }])
                ->orderBy('data_limite')
                ->get()
                ->filter(function($agendamento) use ($user) {
                    // Só mostra se o agendamento para o user NÃO está concluído
                    $pivot = $agendamento->users->first()?->pivot;
                    return !$pivot || $pivot->estado !== 'Concluída';
                })
                ->map(function($agendamento) {
                    // Marca como overdue se não concluída e data_limite passou
                    $hoje = Carbon::today();
                    $isOverdue = false;
                    if(isset($agendamento->data_limite) && $agendamento->data_limite < $hoje) {
                        $isOverdue = true;
                    }
                    $agendamento->isOverdue = $isOverdue;
                    return $agendamento;
                });

            // Prepara eventos para o calendário, incluindo recorrentes
            foreach ($proximosAgendamentos as $agendamento) {
                $cor = $agendamento->isOverdue ? '#c62828' : match(strtolower($agendamento->prioridade ?? '')) {
                    'alta' => '#e53935',
                    'média', 'media' => '#fbc02d',
                    'baixa' => '#43a047',
                    default => '#1976d2',
                };
                $hora = $agendamento->hora_limite ? (is_string($agendamento->hora_limite) ? $agendamento->hora_limite : $agendamento->hora_limite->format('H:i')) : null;
                $title = $agendamento->task->title ?? 'Tarefa';
                if ($hora) {
                    $title .= ' (' . $hora . ')';
                }

                // Se for recorrente, gerar eventos para cada dia do período
                if ($agendamento->repetir && $agendamento->period && $agendamento->initial_date && $agendamento->final_date) {
                    $start = $agendamento->initial_date instanceof \Carbon\Carbon ? $agendamento->initial_date->copy() : Carbon::parse($agendamento->initial_date);
                    $end = $agendamento->final_date instanceof \Carbon\Carbon ? $agendamento->final_date->copy() : Carbon::parse($agendamento->final_date);
                    $period = strtolower($agendamento->period);
                    $current = $start->copy();
                    while ($current->lte($end)) {
                        $addEvent = false;
                        if (in_array($period, ['diario','diária','diaria','daily','day'])) {
                            $addEvent = true;
                        } elseif (in_array($period, ['semanal','weekly','week'])) {
                            if (is_array($agendamento->days_of_week) && count($agendamento->days_of_week)) {
                                if (in_array($current->dayOfWeek, $agendamento->days_of_week)) {
                                    $addEvent = true;
                                }
                            } else {
                                if ($current->dayOfWeek === $start->dayOfWeek) {
                                    $addEvent = true;
                                }
                            }
                        } elseif (in_array($period, ['mensal','monthly','month'])) {
                            if ($current->day === $start->day) {
                                $addEvent = true;
                            }
                        }
                        if ($addEvent) {
                            $eventosCalendario[] = [
                                'title' => $title,
                                'start' => $current->format('Y-m-d'),
                                'color' => $cor,
                                'description' => $agendamento->description ?? '',
                                'prioridade' => $agendamento->prioridade ?? '',
                                'recorrencia' => $agendamento->period ?? '',
                            ];
                        }
                        $current->addDay();
                    }
                } else {
                    // Não recorrente: evento único
                    $dataEvento = $agendamento->data_limite ?? $agendamento->initial_date;
                    if ($dataEvento instanceof \Carbon\Carbon) {
                        $dataEvento = $dataEvento->format('Y-m-d');
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}/', $dataEvento)) {
                        $dataEvento = substr($dataEvento, 0, 10);
                    }
                    $eventosCalendario[] = [
                        'title' => $title,
                        'start' => $dataEvento,
                        'color' => $cor,
                        'description' => $agendamento->description ?? '',
                        'prioridade' => $agendamento->prioridade ?? '',
                        'recorrencia' => $agendamento->period ?? '',
                    ];
                }
            }
        }
        $totalTarefasAtrasadas = $proximosAgendamentos->where('isOverdue', true)->count();

    return view('backoffice.dashboard.index', compact(
        'labels',
        'data',
        'pedidosPendentes',
        'pedidosPrioritarios',
        'proximosAgendamentos',
        'eventosCalendario',
        'totalPedidosPendentes',
        'totalPedidosAgendados',
        'totalPedidosPorAtribuir',
        'totalAguardaPeca',
        'totalConcluidosHoje',
        'totalConcluidosSemana',
        'totalTarefasAtrasadas'
    ));
    }


}
