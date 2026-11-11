<?php

namespace App\Http\Controllers;

use App\Models\BoardTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Quadro: dar uma coisa a alguém para fazer, ver quem pegou nela, responder e dar por feita.
 * Toda a gente vê o quadro todo — é a graça de ser um quadro. Os técnicos entram,
 * senão não havia a quem dar tarefas; só não mexem nas dos outros.
 */
class BoardTaskController extends Controller
{
    /** Quantos dias de tarefas feitas ficam à vista antes de se pedir "ver tudo". */
    private const DIAS_FEITAS = 14;

    public function index(Request $request)
    {
        $eu = auth()->id();
        $tecnico = auth()->user()->hasRole('user');

        // Os técnicos abrem no "só as minhas"; os outros veem tudo
        $sofilter = $request->has('minhas')
            ? $request->boolean('minhas')
            : $tecnico;

        $base = BoardTask::with(['responsavel', 'creator', 'concluidor', 'comments.user'])
            ->withCount('comments')
            ->when($sofilter, fn ($q) => $q->where('assigned_to', $eu))
            ->when($request->filled('quem'), fn ($q) => $q->where('assigned_to', $request->quem));

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
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'assigned_to' => ['required', 'exists:users,id'],
            'prazo' => ['nullable', 'date'],
            'descricao' => ['nullable', 'string', 'max:5000'],
        ], [
            'titulo.required' => __('Escreva o que é para fazer.'),
            'assigned_to.required' => __('Escolha para quem é.'),
        ]);

        $tarefa = BoardTask::create($data + ['estado' => 'por_fazer', 'created_by' => auth()->id()]);

        flash(__('Tarefa dada a :quem.', ['quem' => $tarefa->responsavel->name ?? '—']))->success();

        return back();
    }

    public function update(Request $request, $id)
    {
        $tarefa = BoardTask::findOrFail($id);
        $this->podeMexer($tarefa);

        $tarefa->update($request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'assigned_to' => ['required', 'exists:users,id'],
            'prazo' => ['nullable', 'date'],
            'descricao' => ['nullable', 'string', 'max:5000'],
        ], [
            'titulo.required' => __('Escreva o que é para fazer.'),
            'assigned_to.required' => __('Escolha para quem é.'),
        ]));

        flash(__('Tarefa alterada.'))->success();

        return back();
    }

    /** Mudar de coluna: pegar nela, dar por feita, ou voltar atrás. */
    public function move(Request $request, $id)
    {
        $tarefa = BoardTask::findOrFail($id);
        $this->podeMexer($tarefa);

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

    public function comment(Request $request, $id)
    {
        $tarefa = BoardTask::findOrFail($id);

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
        $tarefa = BoardTask::findOrFail($id);
        $this->podeMexer($tarefa, true);

        $tarefa->delete();
        flash(__('Tarefa apagada.'))->success();

        return back();
    }

    // ----------------------------------------------------------------------

    /**
     * Mexer numa tarefa: quem a tem, quem a deu, ou quem não é técnico
     * (para o coordenador poder arrumar o quadro).
     */
    private function podeMexer(BoardTask $tarefa, bool $apagar = false): void
    {
        $eu = auth()->id();
        $tecnico = auth()->user()->hasRole('user');

        $pode = !$tecnico
            || $tarefa->created_by === $eu
            || (!$apagar && $tarefa->assigned_to === $eu);

        abort_unless($pode, 403, __('Esta tarefa não é sua.'));
    }

    /** A quem se podem dar tarefas: toda a gente que entra no backoffice. */
    private function pessoas()
    {
        return User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']);
    }
}
