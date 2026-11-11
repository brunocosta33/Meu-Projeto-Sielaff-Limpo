<?php

namespace App\Http\Controllers;

use App\Exports\ToolDeliveriesExport;
use App\Models\Item;
use App\Models\ToolDelivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Entregas a Técnicos: o registo de quem levou que peça ou ferramenta, quando e porquê.
 * Não mexe em stock nem nos movimentos do armazém — é um registo à parte.
 * Pode usar toda a gente menos os técnicos.
 */
class ToolDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $this->guard();

        // O aviso do ProAlpha conta tudo o que está por lançar, nao so o que o filtro mostra:
        // é um lembrete, nao um resultado de pesquisa
        $porLancar = ToolDelivery::porLancar();

        return view('backoffice.deliveries.index', [
            'deliveries' => $this->filtered($request)->with(['technician', 'creator', 'proalphaUser', 'editor'])
                ->orderByDesc('ocorrido_em')->orderByDesc('id')
                ->paginate(40)->withQueryString(),
            'numPorLancar' => (clone $porLancar)->count(),
            'maisAntigaPorLancar' => (clone $porLancar)->min('ocorrido_em'),
            'technicians' => $this->technicians(),
            // Por número, não por texto: as referências têm 9, 10 e 12 dígitos e
            // em ordem alfabética a de 10 dígitos aparecia antes da de 9
            'artigos' => Item::where('is_active', true)
                ->orderByRaw('CAST(reference AS UNSIGNED) = 0')
                ->orderByRaw('CAST(reference AS UNSIGNED)')
                ->orderBy('reference')
                ->get(['id', 'reference', 'name']),
            'tipos' => ToolDelivery::TIPOS,
        ]);
    }

    public function store(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(ToolDelivery::TIPOS))],
            'technician_id' => ['required', 'exists:users,id'],
            'item_id' => ['nullable', 'required_without:novo_nome', 'exists:items,id'],
            'novo_nome' => ['nullable', 'required_without:item_id', 'string', 'max:255'],
            'novo_codigo' => ['nullable', 'required_with:novo_nome', 'string', 'max:100', 'unique:items,reference'],
            'quantidade' => ['required', 'integer', 'min:1', 'max:9999'],
            'ocorrido_em' => ['required', 'date', 'before_or_equal:now'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'proalpha' => ['nullable', 'boolean'],
        ], [
            'technician_id.required' => __('Escolha o técnico.'),
            'item_id.required_without' => __('Escolha o artigo da lista ou crie um novo.'),
            'novo_nome.required_without' => __('Dê um nome ao artigo novo.'),
            'novo_codigo.required_with' => __('Dê um código ao artigo novo.'),
            'novo_codigo.unique' => __('Já existe um artigo com esse código.'),
            'quantidade.min' => __('A quantidade tem de ser pelo menos 1.'),
            'ocorrido_em.required' => __('Indique a data e a hora.'),
            'ocorrido_em.before_or_equal' => __('A data e hora não podem ser no futuro.'),
        ]);

        // Criar aqui o artigo acrescenta-o ao catálogo com stock zero; não mexe em quantidades
        $artigo = empty($data['item_id'])
            ? Item::create(['reference' => $data['novo_codigo'], 'name' => $data['novo_nome'], 'is_active' => true])
            : Item::findOrFail($data['item_id']);

        $entrega = ToolDelivery::create([
            'tipo' => $data['tipo'],
            'technician_id' => $data['technician_id'],
            'quantidade' => $data['quantidade'],
            'ocorrido_em' => $data['ocorrido_em'],
            'observacoes' => $data['observacoes'] ?? null,
            // Normalmente lança-se no ProAlpha mais tarde; quem o fizer na hora marca já aqui
            'proalpha_em' => $request->boolean('proalpha') ? now() : null,
            'proalpha_por' => $request->boolean('proalpha') ? auth()->id() : null,
            // Nome e código ficam escritos no registo, para a linha valer por si
            'item_id' => $artigo->id,
            'artigo' => $artigo->name,
            'codigo' => $artigo->reference,
            'created_by' => auth()->id(),
        ]);

        flash($entrega->isEntrega()
            ? __('Entregue :qtd x :artigo a :quem.', ['qtd' => $entrega->quantidade, 'artigo' => $entrega->artigo, 'quem' => $entrega->technician->name])
            : __('Devolvido :qtd x :artigo por :quem.', ['qtd' => $entrega->quantidade, 'artigo' => $entrega->artigo, 'quem' => $entrega->technician->name]))->success();

        return back();
    }

    /**
     * Corrigir um registo já gravado.
     * Fica escrito quem corrigiu e quando; e se mexer no que vai para o ProAlpha,
     * a entrega volta a ficar por lançar, porque o ProAlpha deixou de estar certo.
     */
    public function update(Request $request, $id)
    {
        $this->guard();

        $entrega = ToolDelivery::findOrFail($id);

        $data = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(ToolDelivery::TIPOS))],
            'technician_id' => ['required', 'exists:users,id'],
            'item_id' => ['required', 'exists:items,id'],
            'quantidade' => ['required', 'integer', 'min:1', 'max:9999'],
            'ocorrido_em' => ['required', 'date', 'before_or_equal:now'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ], [
            'technician_id.required' => __('Escolha o técnico.'),
            'item_id.required' => __('Escolha o artigo.'),
            'quantidade.min' => __('A quantidade tem de ser pelo menos 1.'),
            'ocorrido_em.required' => __('Indique a data e a hora.'),
            'ocorrido_em.before_or_equal' => __('A data e hora não podem ser no futuro.'),
        ]);

        $artigo = Item::findOrFail($data['item_id']);

        $entrega->fill([
            'tipo' => $data['tipo'],
            'technician_id' => $data['technician_id'],
            'quantidade' => $data['quantidade'],
            'ocorrido_em' => $data['ocorrido_em'],
            'observacoes' => $data['observacoes'] ?? null,
            'item_id' => $artigo->id,
            'artigo' => $artigo->name,
            'codigo' => $artigo->reference,
        ]);

        // Corrigir só as observações (ou a hora) não desfaz o lançamento no ProAlpha
        $mexeuNoProalpha = (bool) array_intersect(ToolDelivery::CAMPOS_PROALPHA, array_keys($entrega->getDirty()));
        $desfezLancamento = $mexeuNoProalpha && $entrega->isProalpha();

        if ($desfezLancamento) {
            $entrega->proalpha_em = null;
            $entrega->proalpha_por = null;
        }

        $entrega->editado_em = now();
        $entrega->editado_por = auth()->id();
        $entrega->save();

        flash($desfezLancamento
            ? __('Entrega corrigida. Como mudou o que vai para o ProAlpha, voltou a ficar por lançar.')
            : __('Entrega corrigida.'))->success();

        return back();
    }

    /** Marcar (ou desmarcar) um registo como lançado no ProAlpha. */
    public function proalpha(Request $request, $id)
    {
        $this->guard();

        $entrega = ToolDelivery::findOrFail($id);
        $lancar = !$request->boolean('desmarcar');

        $entrega->update([
            'proalpha_em' => $lancar ? now() : null,
            'proalpha_por' => $lancar ? auth()->id() : null,
        ]);

        flash($lancar ? __('Marcado como lançado no ProAlpha.') : __('Voltou a ficar por lançar no ProAlpha.'))->success();

        return back();
    }

    /** Lançar vários de uma vez: na prática passa-se um lote no ProAlpha e marca-se tudo junto. */
    public function proalphaBulk(Request $request)
    {
        $this->guard();

        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ], ['ids.required' => __('Escolha pelo menos um registo.')])['ids'];

        $total = ToolDelivery::whereIn('id', $ids)->porLancar()
            ->update(['proalpha_em' => now(), 'proalpha_por' => auth()->id()]);

        flash($total
            ? trans_choice(':count registo marcado como lançado no ProAlpha.|:count registos marcados como lançados no ProAlpha.', $total, ['count' => $total])
            : __('Nada a marcar: já estavam todos lançados.'))->success();

        return back();
    }

    public function destroy($id)
    {
        $this->guard();

        ToolDelivery::findOrFail($id)->delete();
        flash(__('Registo apagado.'))->success();

        return back();
    }

    public function export(Request $request)
    {
        $this->guard();

        return Excel::download(new ToolDeliveriesExport($this->filtered($request)), 'entregas-' . now()->format('Y-m-d') . '.xlsx');
    }

    // ----------------------------------------------------------------------

    /** Os mesmos filtros servem a página e a exportação. */
    private function filtered(Request $request)
    {
        return ToolDelivery::query()
            ->when($request->filled('technician_id'), fn ($q) => $q->where('technician_id', $request->technician_id))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('proalpha'), fn ($q) => $request->proalpha === 'por_lancar'
                ? $q->porLancar()
                : $q->lancadas())
            ->when($request->filled('de'), fn ($q) => $q->whereDate('ocorrido_em', '>=', $request->de))
            ->when($request->filled('ate'), fn ($q) => $q->whereDate('ocorrido_em', '<=', $request->ate))
            ->when($request->filled('q'), function ($q) use ($request) {
                $termo = '%' . trim($request->q) . '%';
                $q->where(fn ($w) => $w->where('artigo', 'like', $termo)
                    ->orWhere('codigo', 'like', $termo)
                    ->orWhere('observacoes', 'like', $termo)
                    ->orWhereHas('technician', fn ($t) => $t->where('name', 'like', $termo)));
            });
    }

    /** Técnicos: quem recebe. */
    private function technicians()
    {
        return User::whereHas('role', fn ($q) => $q->where('name', 'user'))->orderBy('name')->get();
    }

    /** Toda a gente menos os técnicos, como no resto do armazém. */
    private function guard(): void
    {
        abort_if(auth()->user()->hasRole('user'), 403, __('Os técnicos não registam entregas.'));
    }
}
