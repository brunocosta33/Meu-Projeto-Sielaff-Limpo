<?php

namespace App\Http\Controllers;

use App\Models\HotlineChecklistCheck;
use App\Models\HotlineChecklistItem;
use App\Services\HotlineChecklist;
use App\Services\HotlineRotation;
use App\Services\HrAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Checklist diária da Hotline.
 *
 * A lista é da chefia (Coordenador e Country Manager): só eles criam, alteram, ordenam
 * e arquivam tarefas. Os hotliners não mexem na lista — só validam o que lhes toca,
 * no dia em que estão de serviço. Os dias e os turnos vêm da escala (HotlineChecklist).
 */
class HotlineChecklistController extends Controller
{
    /** Quantos dias do histórico ficam à vista. */
    private const DIAS_HISTORICO = 14;

    /** Quem manda na lista. */
    private const GESTORES = ['coordenador', 'country_manager'];

    public function index(Request $request)
    {
        $this->guard();

        $dia = $this->dia($request);
        $escala = (new HotlineRotation())->schedule($dia, $dia)[0];
        $todos = HotlineChecklistItem::ativos()->ordenados()->get();

        // Ao olhar para trás, a lista é a que existia nesse dia: uma tarefa criada hoje
        // não pode aparecer como "por validar" na semana passada
        $itens = $todos->filter(fn (HotlineChecklistItem $i) => $i->created_at <= $dia->copy()->endOfDay())->values();

        $checks = HotlineChecklistCheck::whereDate('data', $dia)->with('user')->get()
            ->keyBy(fn (HotlineChecklistCheck $c) => $c->item_id . '_' . $c->user_id);

        $quemValida = HotlineChecklist::quemValida($dia, $escala);
        $colunas = HotlineChecklist::colunas($escala);

        // Quanto é que cada um já validou nesse dia, do que lhe tocava
        $resumo = $colunas->map(function (array $col) use ($itens, $checks) {
            $aplica = HotlineChecklist::itensDoTurno($itens, $col['turno']);
            $feitas = $col['quem']
                ? $aplica->filter(fn (HotlineChecklistItem $i) => $checks->has($i->id . '_' . $col['quem']->id))
                : collect();

            return ['total' => $aplica->count(), 'feitas' => $feitas->count()];
        });

        return view('backoffice.hotline.checklist.index', [
            'dia' => $dia,
            'hoje' => $dia->isToday(),
            'colunas' => $colunas,
            'resumo' => $resumo,
            'itens' => $itens,
            'checks' => $checks,
            'turnos' => HotlineChecklistItem::TURNOS,
            // O turno de quem está a ver: null se hoje não é dia dele
            'meuTurno' => $quemValida->get(auth()->id()),
            'gestor' => $this->podeGerir(),
            // Para a chefia: o que ficou por validar ontem, que é quando já não se pode emendar
            'falhasDeOntem' => $this->podeGerir() ? $this->falhasDe(today()->subDay(), $todos) : collect(),
            'historico' => HotlineChecklist::historico(
                today()->subDays(self::DIAS_HISTORICO - 1),
                today(),
                $todos
            ),
            'eu' => auth()->id(),
        ]);
    }

    public function store(Request $request)
    {
        $this->guardGestor();

        $data = $this->dadosDoItem($request);
        $data['ordem'] = (int) HotlineChecklistItem::max('ordem') + 1;
        $data['created_by'] = auth()->id();

        HotlineChecklistItem::create($data);
        flash(__('Tarefa acrescentada à checklist.'))->success();

        return back();
    }

    public function update(Request $request, $id)
    {
        $this->guardGestor();

        HotlineChecklistItem::findOrFail($id)->update($this->dadosDoItem($request));
        flash(__('Tarefa alterada.'))->success();

        return back();
    }

    /** Subir ou descer uma tarefa na lista. */
    public function ordem(Request $request, $id)
    {
        $this->guardGestor();

        $sobe = $request->validate(['dir' => ['required', Rule::in(['cima', 'baixo'])]])['dir'] === 'cima';

        // A ordem pode vir desalinhada (tarefas antigas, arquivadas pelo meio): alinhar primeiro
        $lista = HotlineChecklistItem::ativos()->ordenados()->get()->values();
        $lista->each(fn (HotlineChecklistItem $i, int $n) => $i->ordem === $n + 1 || $i->update(['ordem' => $n + 1]));

        $i = $lista->search(fn (HotlineChecklistItem $x) => $x->id === (int) $id);
        abort_if($i === false, 404);

        $vizinho = $lista->get($sobe ? $i - 1 : $i + 1);

        if ($vizinho) {
            $atual = $lista[$i];
            [$a, $b] = [$atual->ordem, $vizinho->ordem];
            $atual->update(['ordem' => $b]);
            $vizinho->update(['ordem' => $a]);
        }

        return back();
    }

    /**
     * Tirar uma tarefa da lista. Se já foi validada alguma vez, fica arquivada
     * em vez de apagada: apagá-la levava o histórico à frente.
     */
    public function destroy($id)
    {
        $this->guardGestor();

        $item = HotlineChecklistItem::findOrFail($id);

        if ($item->checks()->exists()) {
            $item->update(['ativo' => false]);
            flash(__('Tarefa arquivada. Deixa de aparecer, mas o histórico fica.'))->success();
        } else {
            $item->delete();
            flash(__('Tarefa apagada.'))->success();
        }

        return back();
    }

    /** Validar uma tarefa: feita, ou com problema (e aí diga-se qual). */
    public function validar(Request $request, $id)
    {
        $this->guard();

        $item = HotlineChecklistItem::ativos()->findOrFail($id);
        $turno = $this->meuTurnoHoje();

        abort_unless($turno, 403, __('Hoje não está de serviço na Hotline.'));
        abort_unless($item->doTurno($turno), 403, __('Esta tarefa não é do seu turno de hoje.'));

        $dados = $request->validate([
            'estado' => ['required', Rule::in(['feito', 'problema'])],
            'nota' => ['nullable', 'required_if:estado,problema', 'string', 'max:1000'],
        ], [
            'nota.required_if' => __('Escreva o que se passou.'),
        ]);

        HotlineChecklistCheck::updateOrCreate(
            ['item_id' => $item->id, 'user_id' => auth()->id(), 'data' => today()->toDateString()],
            ['estado' => $dados['estado'], 'nota' => $dados['nota'] ?? null]
        );

        flash($dados['estado'] === 'problema'
            ? __('Ficou registado o problema.')
            : __('Validada.'))->success();

        return back();
    }

    /** Enganou-se? Desfaz-se, mas só no próprio dia. */
    public function desfazer($id)
    {
        $this->guard();

        abort_unless($this->meuTurnoHoje(), 403, __('Hoje não está de serviço.'));

        HotlineChecklistCheck::where('item_id', $id)
            ->where('user_id', auth()->id())
            ->whereDate('data', today())
            ->delete();

        flash(__('Validação desfeita.'))->success();

        return back();
    }

    // ----------------------------------------------------------------------

    /** Os campos de uma tarefa da lista, validados. */
    private function dadosDoItem(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'nota' => ['nullable', 'string', 'max:1000'],
            'turno' => ['required', Rule::in(array_keys(HotlineChecklistItem::TURNOS))],
        ], [
            'titulo.required' => __('Escreva o que é para fazer todos os dias.'),
            'turno.required' => __('Escolha o turno.'),
        ]);
    }

    /** Quem deixou tarefas por validar num dia, e quais. */
    private function falhasDe(Carbon $dia, Collection $itens): Collection
    {
        $escala = (new HotlineRotation())->schedule($dia, $dia)[0];

        // Uma tarefa criada hoje não pode contar como falhada ontem
        $daAltura = $itens->filter(fn (HotlineChecklistItem $i) => $i->created_at <= $dia->copy()->endOfDay());

        $checks = HotlineChecklistCheck::whereDate('data', $dia)->get()->groupBy('user_id');

        return HotlineChecklist::colunas($escala)
            ->filter(fn (array $c) => $c['quem'] && $c['turno'])
            ->map(function (array $c) use ($daAltura, $checks) {
                $feitos = ($checks->get($c['quem']->id) ?? collect())->pluck('item_id');
                $faltam = HotlineChecklist::itensDoTurno($daAltura, $c['turno'])->whereNotIn('id', $feitos);

                return [
                    'nome' => $c['quem']->name,
                    'tarefas' => $faltam->pluck('titulo')->implode(' · '),
                    'n' => $faltam->count(),
                ];
            })
            ->filter(fn (array $f) => $f['n'] > 0)
            ->values();
    }

    /** O dia que se está a ver. Por omissão, hoje. */
    private function dia(Request $request): Carbon
    {
        try {
            $dia = $request->filled('dia') ? Carbon::parse($request->dia)->startOfDay() : today();
        } catch (\Throwable $e) {
            $dia = today();
        }

        // Nem antes de a checklist existir, nem meses à frente
        return $dia->between(today()->subYear(), today()->addDays(14)) ? $dia : today();
    }

    /** O turno de quem está a ver, hoje — null se hoje não é dia dele. */
    private function meuTurnoHoje(): ?string
    {
        return HotlineChecklist::quemValida(today())->get(auth()->id());
    }

    /** A checklist é do escritório; os técnicos não entram. */
    private function guard(): void
    {
        abort_if(auth()->user()->hasRole('user'), 403, __('A checklist da Hotline não é da sua área.'));
    }

    private function guardGestor(): void
    {
        abort_unless($this->podeGerir(), 403, __('Só o Coordenador e o Country Manager mexem na checklist.'));
    }

    private function podeGerir(): bool
    {
        return in_array(HrAccess::staffOf(auth()->user())?->funcao, self::GESTORES, true);
    }
}
