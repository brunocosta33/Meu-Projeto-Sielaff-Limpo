<?php

namespace App\Services;

use App\Models\HotlineChecklistCheck;
use App\Models\HotlineChecklistItem;
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
     * Um dia da escala pronto para a checklist: uma coluna por hotliner.
     * 'quem' é quem lá está mesmo nesse dia — pode ser um substituto.
     */
    public static function colunas(?array $diaEscala = null, ?Carbon $dia = null): Collection
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
                'substituto' => (bool) ($c['override']?->substitute_user_id) && $c['shift'],
            ];
        });
    }

    /** Porque é que não está de serviço (ou quem o substitui). */
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
     * Quem tem de validar nesse dia: user_id => turno.
     * Se a mesma pessoa cobrir os dois turnos, fica com a lista toda.
     */
    public static function quemValida(Carbon $dia, ?array $diaEscala = null): Collection
    {
        return self::colunas($diaEscala, $dia)
            ->filter(fn (array $c) => $c['quem'] && $c['turno'])
            ->groupBy(fn (array $c) => $c['quem']->id)
            ->map(fn (Collection $g) => $g->pluck('turno')->unique()->count() > 1 ? 'ambos' : $g->first()['turno']);
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
     * Resumo de um intervalo de dias, para o histórico:
     * por cada dia, e por cada hotliner, quantas fez de quantas — ou porque não fez nenhuma.
     */
    public static function historico(Carbon $de, Carbon $ate, Collection $itens): array
    {
        $checks = HotlineChecklistCheck::whereBetween('data', [$de->toDateString(), $ate->toDateString()])
            ->get()
            ->groupBy(fn (HotlineChecklistCheck $c) => $c->data->toDateString() . '_' . $c->user_id);

        $dias = [];
        foreach ((new HotlineRotation())->schedule($de, $ate) as $diaEscala) {
            $data = $diaEscala['date'];
            // A lista de hoje não se aplica ao passado: uma tarefa criada ontem
            // não pode contar como falhada na semana passada
            $daAltura = $itens->filter(fn (HotlineChecklistItem $i) => $i->created_at <= $data->copy()->endOfDay());

            $linhas = [];
            foreach (self::colunas($diaEscala) as $staffId => $col) {
                $feitas = collect($checks->get($data->toDateString() . '_' . ($col['quem']->id ?? 0), []));
                $esperadas = self::itensDoTurno($daAltura, $col['turno']);

                $linhas[$staffId] = [
                    'quem' => $col['quem'],
                    'motivo' => $col['motivo'],
                    'total' => $esperadas->count(),
                    'feitas' => $feitas->whereIn('item_id', $esperadas->pluck('id'))->count(),
                    'problemas' => $feitas->where('estado', 'problema')->whereIn('item_id', $esperadas->pluck('id'))->count(),
                ];
            }

            $dias[] = ['data' => $data->copy(), 'linhas' => $linhas];
        }

        return array_reverse($dias);
    }
}
