<?php

namespace App\Http\Controllers;

use App\Exports\HrExpensesExport;
use App\Models\HrExpense;
use App\Models\HrExpenseFile;
use App\Models\HrStaff;
use App\Services\HrAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Recursos Humanos > Despesas.
 * Cada colaborador lança as suas e anexa os comprovativos; a direção aprova ou rejeita,
 * e marca como paga (hr.expense_approver_functions). A gestão de RH vê as de todos.
 */
class HrExpenseController extends Controller
{
    // Os comprovativos ficam no disco privado: só abrem com sessão iniciada
    private const DISK = 'local';

    public function index(Request $request)
    {
        $user = auth()->user();
        $manager = HrAccess::isManager($user);
        $ownStaffId = HrAccess::staffOf($user)?->id;

        $base = $this->filteredQuery($request);

        // Os totais ignoram o filtro do estado, para se poder saltar entre eles
        $counts = (clone $base)->toBase()->selectRaw('estado, COUNT(*) as total, SUM(valor) as soma')->groupBy('estado')->get()->keyBy('estado');
        $porPagar = (clone $base)->toPay();

        $expenses = $this->withStateFilter(clone $base, $request)
            ->with(['staff.user', 'files', 'decider', 'payer'])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('backoffice.hr.expenses.index', [
            'expenses' => $expenses,
            'counts' => $counts,
            'totalPorPagar' => (clone $porPagar)->sum('valor'),
            'numPorPagar' => (clone $porPagar)->count(),
            'states' => HrExpense::STATES,
            'categories' => config('hr.expense_categories'),
            'paymentMethods' => config('hr.expense_payment_methods'),
            'staff' => $manager ? HrStaff::with('user')->where('ativo', true)->get()->sortBy(fn ($s) => $s->user->name ?? '') : collect(),
            'isManager' => $manager,
            'canApprove' => HrAccess::canApproveExpenses($user),
            'ownStaffId' => $ownStaffId,
        ]);
    }

    /** O mesmo que está à vista, em Excel. */
    public function export(Request $request)
    {
        $query = $this->withStateFilter($this->filteredQuery($request), $request);

        return Excel::download(new HrExpensesExport($query), 'despesas-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function store(Request $request)
    {
        $data = $this->validateExpense($request);
        $request->validate($this->fileRules(), $this->fileMessages());

        $expense = HrExpense::create($data + [
            'estado' => 'pendente',
            'created_by' => auth()->id(),
        ]);

        $total = $this->storeFiles($request, $expense);

        flash($total
            ? trans_choice('Despesa lançada com :count comprovativo.|Despesa lançada com :count comprovativos.', $total, ['count' => $total])
            : __('Despesa lançada. Não se esqueça de anexar o comprovativo.'))->success();

        return back();
    }

    /** Corrigir uma despesa enquanto ninguém decidiu sobre ela. */
    public function update(Request $request, $id)
    {
        $expense = HrExpense::findOrFail($id);
        $this->authorizeOwn($expense);
        abort_unless($expense->isOpen(), 422, __('Esta despesa já foi decidida e não pode ser alterada.'));

        $expense->update($this->validateExpense($request, $expense));
        flash(__('Despesa atualizada.'))->success();

        return back();
    }

    public function destroy($id)
    {
        $expense = HrExpense::with('files')->findOrFail($id);
        $this->authorizeOwn($expense);
        abort_unless($expense->isOpen() || HrAccess::isManager(auth()->user()), 422, __('Esta despesa já foi decidida e não pode ser apagada.'));

        foreach ($expense->files as $file) {
            Storage::disk(self::DISK)->delete($file->path);
        }
        $expense->delete();
        flash(__('Despesa apagada.'))->success();

        return back();
    }

    public function approve($id)
    {
        $expense = $this->forDecision($id);
        $expense->update([
            'estado' => 'aprovada',
            'motivo_rejeicao' => null,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);
        flash(__('Despesa aprovada. Falta marcar o pagamento.'))->success();

        return back();
    }

    public function reject(Request $request, $id)
    {
        $expense = $this->forDecision($id);
        $data = $request->validate(
            ['motivo_rejeicao' => ['required', 'string', 'max:2000']],
            ['motivo_rejeicao.required' => __('Indique porque está a rejeitar a despesa.')]
        );

        $expense->update($data + [
            'estado' => 'rejeitada',
            'pago_em' => null,
            'pago_como' => null,
            'pago_por' => null,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);
        flash(__('Despesa rejeitada.'))->success();

        return back();
    }

    /** Marcar que já foi paga (passo à parte da aprovação). */
    public function pay(Request $request, $id)
    {
        $expense = $this->forDecision($id);
        abort_unless($expense->estado === 'aprovada', 422, __('Só se paga uma despesa aprovada.'));

        $data = $request->validate([
            'pago_em' => ['required', 'date', 'before_or_equal:today'],
            'pago_como' => ['nullable', Rule::in(array_keys(config('hr.expense_payment_methods')))],
        ], ['pago_em.before_or_equal' => __('A data do pagamento não pode ser no futuro.')]);

        $expense->update($data + ['pago_por' => auth()->id()]);
        flash(__('Despesa marcada como paga.'))->success();

        return back();
    }

    /** Afinal não estava paga. */
    public function unpay($id)
    {
        $expense = $this->forDecision($id);
        $expense->update(['pago_em' => null, 'pago_como' => null, 'pago_por' => null]);
        flash(__('Pagamento desmarcado.'))->success();

        return back();
    }

    public function attach(Request $request, $id)
    {
        $expense = HrExpense::findOrFail($id);
        $this->authorizeOwn($expense);

        $request->validate(
            ['anexos' => ['required', 'array', 'max:10']] + $this->fileRules(),
            $this->fileMessages() + ['anexos.required' => __('Escolha pelo menos um comprovativo.')]
        );

        $total = $this->storeFiles($request, $expense);
        flash(trans_choice(':count comprovativo anexado.|:count comprovativos anexados.', $total, ['count' => $total]))->success();

        return back();
    }

    public function showFile($fileId)
    {
        $file = HrExpenseFile::with('expense')->findOrFail($fileId);
        $this->authorizeView($file->expense);
        abort_unless(Storage::disk(self::DISK)->exists($file->path), 404);

        return Storage::disk(self::DISK)->response($file->path, $file->nome);
    }

    public function deleteFile($fileId)
    {
        $file = HrExpenseFile::with('expense')->findOrFail($fileId);
        $this->authorizeOwn($file->expense);
        abort_unless($file->expense->isOpen() || HrAccess::isManager(auth()->user()), 422, __('Esta despesa já foi decidida.'));

        Storage::disk(self::DISK)->delete($file->path);
        $file->delete();
        flash(__('Comprovativo removido.'))->success();

        return back();
    }

    // ----------------------------------------------------------------------

    private function validateExpense(Request $request, ?HrExpense $expense = null): array
    {
        $manager = HrAccess::isManager(auth()->user());
        $ownStaffId = HrAccess::staffOf(auth()->user())?->id;

        $data = $request->validate([
            'hr_staff_id' => [$manager ? 'required' : 'nullable', 'exists:hr_staff,id'],
            'data' => ['required', 'date', 'before_or_equal:today'],
            'categoria' => ['required', Rule::in(array_keys(config('hr.expense_categories')))],
            'descricao' => ['nullable', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ], [
            'hr_staff_id.required' => __('Escolha o colaborador.'),
            'data.required' => __('Indique a data da despesa.'),
            'data.before_or_equal' => __('A data da despesa não pode ser no futuro.'),
            'categoria.required' => __('Escolha a categoria.'),
            'valor.required' => __('Indique o valor.'),
            'valor.min' => __('O valor tem de ser maior que zero.'),
        ]);

        // Quem não é gestão de RH só lança despesas suas
        if (!$manager) {
            abort_unless($ownStaffId, 403, __('Não tem ficha de colaborador.'));
            $data['hr_staff_id'] = $ownStaffId;
        }

        return $data;
    }

    /** Filtros da página (sem o estado): quem não é gestão de RH só vê as suas. */
    private function filteredQuery(Request $request)
    {
        $manager = HrAccess::isManager(auth()->user());
        $ownStaffId = HrAccess::staffOf(auth()->user())?->id;

        return HrExpense::query()
            ->when(!$manager, fn ($q) => $q->where('hr_staff_id', $ownStaffId ?? 0))
            ->when($request->filled('colaborador'), fn ($q) => $q->where('hr_staff_id', $request->colaborador))
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->categoria))
            ->when($request->filled('de'), fn ($q) => $q->whereDate('data', '>=', $request->de))
            ->when($request->filled('ate'), fn ($q) => $q->whereDate('data', '<=', $request->ate));
    }

    /** 'por_pagar' não é um estado: são as aprovadas que ainda não foram pagas. */
    private function withStateFilter($query, Request $request)
    {
        return $query->when($request->filled('estado'), fn ($q) => $request->estado === 'por_pagar'
            ? $q->where('estado', 'aprovada')->whereNull('pago_em')
            : $q->where('estado', $request->estado));
    }

    /** A despesa existe e quem está a ver pode mexer-lhe (o próprio ou a gestão de RH). */
    private function authorizeOwn(HrExpense $expense): void
    {
        abort_unless(
            HrAccess::isManager(auth()->user()) || $expense->hr_staff_id === HrAccess::staffOf(auth()->user())?->id,
            403
        );
    }

    private function authorizeView(HrExpense $expense): void
    {
        $this->authorizeOwn($expense);
    }

    private function forDecision($id): HrExpense
    {
        abort_unless(HrAccess::canApproveExpenses(auth()->user()), 403, __('Só a direção decide sobre despesas.'));

        return HrExpense::findOrFail($id);
    }

    private function fileRules(): array
    {
        return [
            'anexos' => ['nullable', 'array', 'max:10'],
            'anexos.*' => ['file', 'mimes:jpg,jpeg,png,heic,heif,webp,pdf,doc,docx,xls,xlsx,csv,txt', 'max:32768'],
        ];
    }

    private function fileMessages(): array
    {
        return [
            'anexos.max' => __('No máximo 10 comprovativos de cada vez.'),
            'anexos.*.mimes' => __('O comprovativo tem de ser imagem, PDF, Word, Excel ou texto.'),
            'anexos.*.max' => __('Cada comprovativo pode ter no máximo 32 MB.'),
        ];
    }

    private function storeFiles(Request $request, HrExpense $expense): int
    {
        $total = 0;
        foreach ((array) $request->file('anexos', []) as $file) {
            $expense->files()->create([
                'path' => $file->store('hr_expenses/' . $expense->id, self::DISK),
                'nome' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'tamanho' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
            $total++;
        }

        return $total;
    }
}
