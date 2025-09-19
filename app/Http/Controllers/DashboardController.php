<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Installation;
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

        // Instalações nos próximos 21 dias
        $hoje = Carbon::today();
        $limite = $hoje->copy()->addDays(21);

        $instalacoesBreve = Installation::whereBetween('scheduled_date', [$hoje, $limite])
            ->with('store')
            ->orderBy('scheduled_date')
            ->get();

        // 🔽 NOVO: Pedidos de assistência com estado pendente ou agendado
        $pedidosPendentes = TechnicalRequest::whereIn('estado', ['pendente', 'agendado'])
            ->with('store')
            ->orderByDesc('data_pedido')
            ->get();

        // Próximos agendamentos do usuário logado (próximos 7 dias)
        $user = auth()->user();
        $hoje = Carbon::today();
        $proximosAgendamentos = collect();
        $eventosCalendario = [];
        if ($user) {
            $proximosAgendamentos = $user->taskSchedules()
                ->where(function($q) use ($hoje) {
                    $q->whereDate('data_limite', '>=', $hoje)
                      ->orWhere(function($q2) use ($hoje) {
                          $q2->whereNotNull('initial_date')->whereDate('initial_date', '>=', $hoje);
                      });
                })
                ->with('task')
                ->orderBy('data_limite')
                ->get();

            // Prepara eventos para o calendário
            foreach ($proximosAgendamentos as $agendamento) {
                // Cor por prioridade
                $cor = match(strtolower($agendamento->prioridade ?? '')) {
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
                // Garante formato YYYY-MM-DD para eventos de dia inteiro
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
        return view('backoffice.dashboard.index', compact('labels', 'data', 'instalacoesBreve', 'pedidosPendentes', 'proximosAgendamentos', 'eventosCalendario'));
    }


}
