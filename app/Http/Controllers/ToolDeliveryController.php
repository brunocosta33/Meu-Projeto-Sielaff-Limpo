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

        return view('backoffice.deliveries.index', [
            'deliveries' => $this->filtered($request)->with(['technician', 'creator'])
                ->orderByDesc('ocorrido_em')->orderByDesc('id')
                ->paginate(40)->withQueryString(),
            'technicians' => $this->technicians(),
            'artigos' => Item::where('is_active', true)->orderBy('name')->get(['id', 'reference', 'name']),
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
