<?php

namespace App\Http\Controllers;

use App\Models\BoardTask;
use App\Models\HrStaff;
use App\Models\User;
use App\Services\HrAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Quadro: dar uma coisa a alguém para fazer, ver quem pegou nela, responder e dar por feita.
 * É do escritório — Coordenador, Country Manager, Administrativa e Hotline. Os técnicos
 * não entram nisto nem aparecem na lista de quem recebe tarefas.
 *
 * O que cada um vê é só o seu: ver apenasVisiveis().
 */
class BoardTaskController extends Controller
{
    /** Quantos dias de tarefas feitas ficam à vista antes de se pedir "ver tudo". */
    private const DIAS_FEITAS = 14;

    public function index(Request $request)
    {
        $this->guard();
        $eu = auth()->id();
        $sofilter = $request->boolean('minhas');

        $base = $this->apenasVisiveis(BoardTask::query())
            ->with(['responsavel', 'creator', 'concluidor', 'comments.user'])
            ->withCount('comments')
            ->when($sofilter, fn ($q) => $q->where('assigned_to', $eu))
            ->when($request->filled('quem'), fn ($q) => $q->where('assigned_to', $request->quem))
            ->when($request->boolean('atraso'), fn ($q) => $q->whereNotNull('prazo')
                ->whereDate('prazo', '<', today())
                ->where('estado', '!=', 'feito'));

        $colunas = [];
        foreach (BoardTask::ESTADOS as $chave => $estado) {
            $q = (clone $base)->where('estado', $chave);

            // Em "Feito" só as últimas semanas, senão a coluna cresce para sempre
            if ($chave === 'feito' && !$request->boolean('tudo')) {
                $q->where('concluido_em', '>=', now()->subDays(self::DIAS_FEITAS));
            }

            $colunas[$chave] = $q
                ->orderByRaw('prazo IS NULL')
                ->orderBy('prazo')
                ->orderByDesc('id')
                ->get();
        }

        return view('backoffice.board.index', [
            'colunas' => $colunas,
            'estados' => BoardTask::ESTADOS,
            'pessoas' => $this->pessoas(),
            'cores' => $this->cores(),
            'equipa' => $this->equipa(),
            'porPessoa' => $this->apenasVisiveis(BoardTask::where('estado', '!=', 'feito'))
                ->selectRaw('assigned_to, COUNT(*) as total')
                ->groupBy('assigned_to')->pluck('total', 'assigned_to'),
            'totalAbertas' => $this->apenasVisiveis(BoardTask::where('estado', '!=', 'feito'))->count(),
            // O prazo só serve para alguma coisa se o atraso saltar à vista
            'numAtrasadas' => $this->apenasVisiveis(BoardTask::where('estado', '!=', 'feito'))
                ->whereNotNull('prazo')->whereDate('prazo', '<', today())->count(),
            'soMinhas' => $sofilter,
            'verTudo' => $request->boolean('tudo'),
            'feitasEscondidas' => $request->boolean('tudo')
                ? 0
                : (clone $base)->where('estado', 'feito')->where('concluido_em', '<', now()->subDays(self::DIAS_FEITAS))->count(),
            'eu' => $eu,
        ]);
    }

    public function store(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'assigned_to' => $this->regraPessoa(),
            'prazo' => ['nullable', 'date', 'after_or_equal:today'],
            'descricao' => ['nullable', 'string', 'max:5000'],
        ], $this->mensagensPessoa() + [
            'titulo.required' => __('Escreva o que é para fazer.'),
            'prazo.after_or_equal' => __('A data limite já passou.'),
        ]);

        $tarefa = BoardTask::create($data + ['estado' => 'por_fazer', 'created_by' => auth()->id()]);

        flash(__('Tarefa dada a :quem.', ['quem' => $tarefa->responsavel->name ?? '—']))->success();

        return back();
    }

    public function update(Request $request, $id)
    {
        $this->guard();
        $tarefa = $this->tarefa($id);

        $tarefa->update($request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'assigned_to' => $this->regraPessoa(),
            'prazo' => ['nullable', 'date'],
            'descricao' => ['nullable', 'string', 'max:5000'],
        ], $this->mensagensPessoa() + [
            'titulo.required' => __('Escreva o que é para fazer.'),
        ]));

        flash(__('Tarefa alterada.'))->success();

        return back();
    }

    /** Mudar de coluna: pegar nela, dar por feita, ou voltar atrás. */
    public function move(Request $request, $id)
    {
        $this->guard();
        $tarefa = $this->tarefa($id);

        $estado = $request->validate([
            'estado' => ['required', Rule::in(array_keys(BoardTask::ESTADOS))],
        ])['estado'];

        $tarefa->estado = $estado;

        // Guardar quem pegou nela e quem a deu por feita; ao voltar atrás, limpar
        if ($estado === 'em_curso' && !$tarefa->iniciado_em) {
            $tarefa->iniciado_em = now();
            $tarefa->iniciado_por = auth()->id();
        }

        if ($estado === 'feito') {
            $tarefa->concluido_em = now();
            $tarefa->concluido_por = auth()->id();
        } else {
            $tarefa->concluido_em = null;
            $tarefa->concluido_por = null;
        }

        if ($estado === 'por_fazer') {
            $tarefa->iniciado_em = null;
            $tarefa->iniciado_por = null;
        }

        $tarefa->save();

        flash(match ($estado) {
            'em_curso' => __('Está consigo. Boa.'),
            'feito' => __('Tarefa concluída.'),
            default => __('Tarefa de volta a Por fazer.'),
        })->success();

        return back();
    }

    /** Passar o ticket a outra pessoa, com nota no fio para não se perder o rasto. */
    public function reassign(Request $request, $id)
    {
        $this->guard();
        $tarefa = $this->tarefa($id);

        $novo = User::findOrFail($request->validate(
            ['assigned_to' => $this->regraPessoa()],
            $this->mensagensPessoa(__('Escolha para quem passa.'))
        )['assigned_to']);

        if ($novo->id === $tarefa->assigned_to) {
            flash(__('A tarefa já era de :quem.', ['quem' => $novo->name]))->warning();

            return back();
        }

        $antigo = $tarefa->responsavel->name ?? '—';

        // Quem recebe ainda não pegou nela: volta a Por fazer
        if ($tarefa->estado === 'em_curso') {
            $tarefa->estado = 'por_fazer';
            $tarefa->iniciado_em = null;
            $tarefa->iniciado_por = null;
        }

        $tarefa->assigned_to = $novo->id;
        $tarefa->save();

        $tarefa->comments()->create([
            'user_id' => auth()->id(),
            'tipo' => 'passagem',
            'texto' => __('Passou de :antigo para :novo.', ['antigo' => $antigo, 'novo' => $novo->name]),
        ]);

        flash(__('Tarefa passada a :quem.', ['quem' => $novo->name]))->success();

        return back();
    }

    public function comment(Request $request, $id)
    {
        $this->guard();
        $tarefa = $this->tarefa($id);

        $texto = $request->validate(
            ['texto' => ['required', 'string', 'max:5000']],
            ['texto.required' => __('Escreva a resposta.')]
        )['texto'];

        $tarefa->comments()->create(['user_id' => auth()->id(), 'texto' => $texto]);
        $tarefa->touch();

        flash(__('Resposta registada.'))->success();

        return back();
    }

    public function destroy($id)
    {
        $this->guard();

        $this->tarefa($id)->delete();
        flash(__('Tarefa apagada.'))->success();

        return back();
    }

    // ----------------------------------------------------------------------

    /** Só se dão tarefas a quem entra no Quadro — nunca a um técnico, nem por pedido forjado. */
    private function regraPessoa(): array
    {
        return ['required', Rule::in($this->pessoas()->pluck('id')->all())];
    }

    private function mensagensPessoa(?string $obrigatorio = null): array
    {
        return [
            'assigned_to.required' => $obrigatorio ?? __('Escolha para quem é.'),
            'assigned_to.in' => __('Essa pessoa não entra no Quadro.'),
        ];
    }

    /** O Quadro é do escritório; os técnicos não entram. */
    private function guard(): void
    {
        abort_if(auth()->user()->hasRole('user'), 403, __('O Quadro não é da sua área.'));
    }

    /**
     * Cada um vê o que é seu. Ninguém vê as tarefas dos outros só por ser chefia:
     * uma tarefa passa a ser vista por outra pessoa quando lha dão ou lha passam.
     *
     * Em concreto, vê-se uma tarefa se:
     *  - está em mãos de quem a está a ver (a Hotline conta como equipa: veem-se uns aos outros);
     *  - foi essa pessoa que a deu a alguém;
     *  - foi essa pessoa que a passou a alguém — para não perder de vista o que entregou.
     */
    private function apenasVisiveis($query)
    {
        $eu = auth()->id();

        // A Hotline trabalha em equipa e pega nos tickets uns dos outros;
        // o resto do escritório só vê o que lhe está nas mãos
        $meus = HrAccess::staffOf(auth()->user())?->funcao === 'hotliner'
            ? HrStaff::where('ativo', true)->where('funcao', 'hotliner')->pluck('user_id')->all()
            : [$eu];

        return $query->where(fn ($q) => $q
            ->whereIn('assigned_to', $meus)
            ->orWhere('created_by', $eu)
            ->orWhereHas('comments', fn ($c) => $c->where('tipo', 'passagem')->where('user_id', $eu)));
    }

    /** A tarefa existe e quem está a ver pode mesmo vê-la. */
    private function tarefa($id): BoardTask
    {
        $tarefa = $this->apenasVisiveis(BoardTask::query())->find($id);

        abort_unless($tarefa, 404);

        return $tarefa;
    }

    /**
     * Uma cor por pessoa, atribuída pela ordem em que entram no Quadro.
     * Pelo id dava colisões (o 1 e o 9 calhavam na mesma cor).
     */
    private function cores()
    {
        return $this->pessoas()->values()->mapWithKeys(
            fn (User $p, int $i) => [$p->id => BoardTask::CORES[$i % count(BoardTask::CORES)]]
        );
    }

    /** A quem se podem dar tarefas: o escritório, sem os técnicos. */
    private function pessoas()
    {
        return User::where('is_active', true)
            ->whereDoesntHave('role', fn ($q) => $q->where('name', 'user'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * As mesmas pessoas, mas arrumadas pela função que têm em RH
     * (Coordenador, Country Manager, Administrativa, Hotline, Técnicos).
     */
    private function equipa()
    {
        $ordem = ['coordenador', 'country_manager', 'administrativa', 'hotliner'];
        $funcoes = HrStaff::where('ativo', true)->pluck('funcao', 'user_id');

        return $this->pessoas()
            ->groupBy(fn (User $u) => $funcoes[$u->id] ?? 'outros')
            ->sortBy(function ($grupo, $funcao) use ($ordem) {
                $i = array_search($funcao, $ordem, true);

                return $i === false ? count($ordem) : $i;
            });
    }
}
