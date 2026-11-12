<?php

namespace App\Services;

use App\Models\HotlineChecklistCheck;
use App\Models\HotlineChecklistItem;
use App\Models\HrShiftOverride;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Liga a checklist diária à escala da Hotline.
 *
 * A lista não tem dias nem horários guardados: quem tem de validar, e em que turno,
 * sai sempre do HotlineRotation. Assim folgas, férias, faltas e substituições já estão
 * tratadas — e um dia de folga nunca conta como falhado.
 */
class HotlineChecklist
{
    /** "Bruno Silva" -> "BS", para as bolinhas. */
    public static function iniciais(?string $nome): string
    {
        return (string) \Illuminate\Support\Str::of($nome ?: '?')
            ->explode(' ')
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->take(2)
            ->implode('');
    }

    /** Quem entra antes das 10:00 está no turno da manhã; o resto, no da tarde. */
    public static function turnoDe(?array $shift): ?string
    {
        if (!$shift) {
            return null;
        }

        return ((int) substr($shift['inicio'], 0, 2)) < 10 ? 'manha' : 'tarde';
    }

    /**
     * Os turnos desse dia, tal como estão na escala: um por hotliner titular.
     * 'quem' é quem o faz mesmo — pode ser outra pessoa, por troca ou substituição.
     */
    public static function slots(?array $diaEscala = null, ?Carbon $dia = null): Collection
    {
        $diaEscala ??= (new HotlineRotation())->schedule($dia, $dia)[0];

        return collect($diaEscala['cells'])->map(function (array $c) {
            $trabalha = $c['shift'] ? $c['worker'] : null;

            return [
                'staff' => $c['staff'],
                'titular' => $c['staff']->user,
                'quem' => $trabalha,
                'turno' => $trabalha ? self::turnoDe($c['shift']) : null,
                'horas' => $c['shift'] ? HotlineRotation::formatHours($c['shift']) : null,
                'motivo' => self::motivo($c),
                'troca' => $c['override']?->motivo === 'troca',
            ];
        });
    }

    /** Porque é que este turno não é feito por quem está escalado. */
    private static function motivo(array $c): ?string
    {
        if (!$c['shift']) {
            return __('Folga');
        }

        if ($c['override'] && !$c['override']->substitute_user_id) {
            return __('A descoberto');
        }

        if (!$c['override'] && $c['vacation']) {
            return __('Férias');
        }

        if (!$c['override'] && $c['absence']) {
            return __('Ausente');
        }

        return null;
    }

    /**
     * As colunas da checklist: uma por pessoa, não uma por turno da escala.
     *
     * É aqui que as trocas ficam certas. Se o Bruno faz o dia do Pedro, a coluna do
     * Bruno mostra o turno que ele está mesmo a fazer e a do Pedro diz que trocou —
     * em vez de o Bruno aparecer duas vezes, uma delas a dizer "Folga".
     */
    public static function colunas(?array $diaEscala = null, ?Carbon $dia = null): Collection
    {
        $slots = self::slots($diaEscala, $dia);

        // Uma coluna por hotliner, pela ordem da escala
        $cols = [];
        foreach ($slots as $s) {
            $cols[$s['titular']->id] = [
                'user' => $s['titular'],
                'daCasa' => true,
                'turno' => null,
                'horas' => null,
                'motivo' => $s['motivo'] ?? __('Folga'),
                'curto' => $s['motivo'] ?? __('Folga'),
                'porConta' => null,
            ];
        }

        foreach ($slots as $s) {
            if (!$s['quem']) {
                continue;
            }

            // Quem vem de fora da escala (uma substituição) ganha coluna própria
            $cols[$s['quem']->id] ??= [
                'user' => $s['quem'],
                'daCasa' => false,
                'turno' => null,
                'horas' => null,
                'motivo' => null,
                'curto' => null,
                'porConta' => null,
            ];

            $col = $cols[$s['quem']->id];
            // Pode estar a fazer os dois turnos (o seu e o de quem substitui)
            $col['turno'] = $col['turno'] && $col['turno'] !== $s['turno'] ? 'ambos' : $s['turno'];
            $col['horas'] = $col['horas'] ? $col['horas'] . ' + ' . $s['horas'] : $s['horas'];
            $col['motivo'] = null;
            $col['curto'] = null;

            if ($s['quem']->id !== $s['titular']->id) {
                $col['porConta'] = $s['titular'];

                // E o dono do turno fica com a razão por que não o faz
                $cols[$s['titular']->id]['motivo'] = $s['troca']
                    ? __('Trocou com :nome', ['nome' => $s['quem']->name])
                    : __('Substituído por :nome', ['nome' => $s['quem']->name]);
                $cols[$s['titular']->id]['curto'] = $s['troca'] ? __('Trocou') : __('Substituído');
            }

            $cols[$s['quem']->id] = $col;
        }

        return collect($cols);
    }

    /**
     * Quem tem de validar nesse dia: user_id => turno.
     * Se a mesma pessoa cobrir os dois turnos, fica com a lista toda.
     */
    public static function quemValida(Carbon $dia, ?array $diaEscala = null): Collection
    {
        return self::colunas($diaEscala, $dia)
            ->filter(fn (array $c) => $c['turno'])
            ->map(fn (array $c) => $c['turno']);
    }

    /** As tarefas que se aplicam a um turno, pela ordem da lista. */
    public static function itensDoTurno(Collection $itens, ?string $turno): Collection
    {
        return $itens->filter(fn (HotlineChecklistItem $i) => $i->doTurno($turno))->values();
    }

    /**
     * Quantas tarefas faltam a alguém hoje — é o número do menu.
     * Devolve 0 a quem não está de serviço (e a quem nada tem a ver com a Hotline).
     */
    public static function porValidar(?User $user, ?Carbon $dia = null): int
    {
        if (!$user) {
            return 0;
        }

        $dia = ($dia ?? Carbon::today())->copy()->startOfDay();
        $turno = self::quemValida($dia)->get($user->id);

        if (!$turno) {
            return 0;
        }

        $itens = self::itensDoTurno(HotlineChecklistItem::ativos()->ordenados()->get(), $turno);

        $feitos = HotlineChecklistCheck::whereDate('data', $dia)
            ->where('user_id', $user->id)
            ->pluck('item_id');

        return $itens->whereNotIn('id', $feitos)->count();
    }

    /**
     * O número do menu. Só se calcula a escala a quem pode mesmo ter checklist hoje
     * (a Hotline, ou quem lá está a cobrir um turno); aos outros fica em zero sem contas.
     */
    public static function badge(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        $daHotline = HrAccess::staffOf($user)?->funcao === 'hotliner';

        $cobre = !$daHotline && HrShiftOverride::whereDate('data', today())
            ->where('substitute_user_id', $user->id)
            ->exists();

        return $daHotline || $cobre ? self::porValidar($user) : 0;
    }

    /**
     * Resumo de um intervalo de dias, para o histórico: por cada dia e por cada pessoa,
     * quantas validou de quantas — ou porque não tinha nenhuma (folga, troca, férias).
     *
     * É por pessoa, não por turno da escala: num dia trocado quem está de folga é quem
     * trocou, e a linha tem de dizer isso mesmo.
     */
    public static function historico(Carbon $de, Carbon $ate, Collection $itens): array
    {
        $checks = HotlineChecklistCheck::whereBetween('data', [$de->toDateString(), $ate->toDateString()])
            ->get()
            ->groupBy(fn (HotlineChecklistCheck $c) => $c->data->toDateString() . '_' . $c->user_id);

        $pessoas = [];
        $dias = [];

        foreach ((new HotlineRotation())->schedule($de, $ate) as $diaEscala) {
            $data = $diaEscala['date'];
            // A lista de hoje não se aplica ao passado: uma tarefa criada ontem
            // não pode contar como falhada na semana passada
            $daAltura = $itens->filter(fn (HotlineChecklistItem $i) => $i->created_at <= $data->copy()->endOfDay());

            $linhas = [];
            foreach (self::colunas($diaEscala) as $uid => $col) {
                // A coluna existe no mapa mesmo que a pessoa só apareça num dos dias
                $pessoas[$uid] ??= $col['user'];

                $feitas = collect($checks->get($data->toDateString() . '_' . $uid, []));
                $esperadas = self::itensDoTurno($daAltura, $col['turno']);
                $ids = $esperadas->pluck('id');

                $linhas[$uid] = [
                    'turno' => $col['turno'],
                    'motivo' => $col['motivo'],
                    'curto' => $col['curto'],
                    'total' => $esperadas->count(),
                    'feitas' => $feitas->whereIn('item_id', $ids)->count(),
                    'problemas' => $feitas->where('estado', 'problema')->whereIn('item_id', $ids)->count(),
                ];
            }

            $dias[] = ['data' => $data->copy(), 'linhas' => $linhas];
        }

        return ['pessoas' => collect($pessoas), 'dias' => array_reverse($dias)];
    }
}
