<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentPermission;
use App\Services\DocumentAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use ZipArchive;

class DocumentController extends Controller
{
    // Ficheiros privados: só abrem com sessão iniciada
    private const DISK = 'local';

    public function index(Request $request)
    {
        $this->authorizeView();
        $query = Document::query()->orderBy('titulo');

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('titulo', 'like', $term)
                ->orWhere('descricao', 'like', $term)
                ->orWhere('file_name', 'like', $term)
                ->orWhere('modelos', 'like', $term));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('modelo')) {
            $request->modelo === 'geral'
                ? $query->where(fn ($q) => $q->whereNull('modelos')->orWhereJsonLength('modelos', 0))
                : $query->whereJsonContains('modelos', $request->modelo);
        }

        $all = Document::get(['tipo', 'modelos']);

        return view('backoffice.documents.index', [
            'documents' => $query->get(),
            'models' => $this->knownModels(),
            'modelGroups' => $this->modelGroups(),
            'typeCounts' => $all->countBy('tipo'),
            'modelCounts' => $all->flatMap(fn ($d) => $d->modelos ?: ['geral'])->countBy(),
            'total' => $all->count(),
            'canManage' => $this->canManage(),
            'canConfigure' => DocumentAccess::canConfigure(auth()->user()),
            'permissions' => DocumentPermission::all()->keyBy('funcao'),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $maxKb = config('documents.max_size_kb');
        $data = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:pdf', 'max:' . $maxKb],
            'titulos' => ['array'],
            'titulos.*' => ['nullable', 'string', 'max:255'],
        ] + $this->metadataRules(), [
            'files.required' => __('Escolha pelo menos um PDF.'),
            'files.*.mimes' => __('Só são aceites ficheiros PDF.'),
            'files.*.max' => __('Cada ficheiro pode ter no máximo :mb MB.', ['mb' => round($maxKb / 1024)]),
        ]);

        $meta = $this->metadata($request);
        foreach ($request->file('files') as $i => $file) {
            $title = trim($data['titulos'][$i] ?? '') ?: $this->titleFromFileName($file->getClientOriginalName());

            Document::create($meta + [
                'titulo' => $title,
                'file_path' => $file->store('documents', self::DISK),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        $count = count($request->file('files'));
        flash($count === 1 ? __('Documento adicionado.') : __(':total documentos adicionados.', ['total' => $count]))->success();

        return redirect()->route('backoffice.documents.index');
    }

    public function edit($id)
    {
        abort_unless($this->canManage(), 403);

        return view('backoffice.documents.edit', [
            'document' => Document::with('uploader')->findOrFail($id),
            'models' => $this->knownModels(),
            'modelGroups' => $this->modelGroups(),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->canManage(), 403);
        $document = Document::findOrFail($id);

        $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:' . config('documents.max_size_kb')],
        ] + $this->metadataRules(), [
            'file.mimes' => __('Só são aceites ficheiros PDF.'),
        ]);

        $document->fill($this->metadata($request) + ['titulo' => $request->titulo]);

        if ($request->hasFile('file')) {
            Storage::disk(self::DISK)->delete($document->file_path);
            $file = $request->file('file');
            $document->fill([
                'file_path' => $file->store('documents', self::DISK),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);
        }

        $document->save();
        flash(__('Documento atualizado.'))->success();

        return redirect()->route('backoffice.documents.index');
    }

    public function destroy($id)
    {
        abort_unless($this->canManage(), 403);
        $document = Document::findOrFail($id);
        Storage::disk(self::DISK)->delete($document->file_path);
        $document->delete();

        flash(__('Documento apagado.'))->success();
        return back();
    }

    /** Leitura no telemóvel: página do site com barra "Voltar" (no computador abre-se o PDF num separador novo). */
    public function read(Request $request, $id)
    {
        $this->authorizeView();
        $document = $this->findWithFile($id);

        // Só aceita voltar para a própria Documentação (evita redirecionamentos para fora)
        $back = (string) $request->query('voltar');
        $backUrl = str_starts_with($back, route('backoffice.documents.index')) ? $back : route('backoffice.documents.index');

        return view('backoffice.documents.viewer', compact('document', 'backUrl'));
    }

    /** Abre o PDF no browser. */
    public function view($id)
    {
        $this->authorizeView();
        $document = $this->findWithFile($id);

        return Storage::disk(self::DISK)->response($document->file_path, $document->download_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . Str::ascii($document->download_name) . '"',
        ]);
    }

    public function download($id)
    {
        $this->authorizeView();
        $document = $this->findWithFile($id);

        return Storage::disk(self::DISK)->download($document->file_path, $document->download_name);
    }

    /** ZIP com os documentos selecionados (ids[]) ou com todos os de um modelo/tipo. */
    public function zip(Request $request)
    {
        $this->authorizeView();
        $query = Document::query();
        $zipName = 'documentacao';

        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
            $zipName = 'documentos-selecionados';
        } elseif ($request->filled('modelo')) {
            $request->modelo === 'geral'
                ? $query->where(fn ($q) => $q->whereNull('modelos')->orWhereJsonLength('modelos', 0))
                : $query->whereJsonContains('modelos', $request->modelo);
            $zipName = 'documentacao-' . Str::slug($request->modelo === 'geral' ? 'geral' : $request->modelo);
        }

        $documents = $query->orderBy('tipo')->orderBy('titulo')->get();
        if ($documents->isEmpty()) {
            flash(__('Não há documentos para exportar.'))->warning();
            return back();
        }

        $tmp = tempnam(sys_get_temp_dir(), 'docs');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $used = [];
        foreach ($documents as $document) {
            $path = Storage::disk(self::DISK)->path($document->file_path);
            if (!is_file($path)) {
                continue;
            }
            // Organizado por pasta do tipo; evita nomes repetidos
            $name = $document->type['label'] . '/' . $document->download_name;
            $base = $name;
            for ($n = 2; isset($used[$name]); $n++) {
                $name = preg_replace('/\.pdf$/', " ($n).pdf", $base);
            }
            $used[$name] = true;
            $zip->addFile($path, $name);
        }
        $zip->close();

        return response()->download($tmp, $zipName . '-' . now()->format('Y-m-d') . '.zip')->deleteFileAfterSend();
    }

    /** O Coordenador escolhe que funções podem ver e gerir a documentação. */
    public function updatePermissions(Request $request)
    {
        abort_unless(DocumentAccess::canConfigure(auth()->user()), 403);

        $owner = config('documents.owner_function');
        foreach (array_keys(config('hr.functions')) as $funcao) {
            if ($funcao === $owner) {
                continue;
            }
            $gerir = $request->boolean("gerir.$funcao");
            DocumentPermission::updateOrCreate(['funcao' => $funcao], [
                'pode_ver' => $gerir || $request->boolean("ver.$funcao"), // quem gere também vê
                'pode_gerir' => $gerir,
                'updated_by' => auth()->id(),
            ]);
        }

        flash(__('Acessos à documentação atualizados.'))->success();
        return back();
    }

    private function metadataRules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(config('documents.types')))],
            'modelos' => ['nullable', 'array'],
            'modelos.*' => ['string', 'max:100'],
            'novo_modelo' => ['nullable', 'string', 'max:255'],
            'idioma' => ['nullable', Rule::in(array_keys(config('documents.languages')))],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function metadata(Request $request): array
    {
        // Modelos escolhidos + novos escritos à mão (separados por vírgula)
        $models = collect($request->input('modelos', []))
            ->merge(explode(',', (string) $request->input('novo_modelo')))
            ->map(fn ($m) => trim($m))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'tipo' => $request->tipo,
            'modelos' => $models ?: null,
            'idioma' => $request->idioma ?: null,
            'descricao' => $request->descricao,
        ];
    }

    /** Modelos por família (configuração) + "Outros" com modelos escritos à mão em documentos. */
    private function modelGroups(): array
    {
        $groups = config('documents.model_groups', []);
        $configured = collect($groups)->flatten();
        $extra = Document::pluck('modelos')->flatten()
            ->map(fn ($m) => trim((string) $m))
            ->filter()
            ->reject(fn ($m) => $configured->contains(fn ($c) => mb_strtolower($c) === mb_strtolower($m)))
            ->unique(fn ($m) => mb_strtolower($m))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return $extra ? $groups + ['Outros' => $extra] : $groups;
    }

    /** Lista simples de todos os modelos (configuração + outros). */
    private function knownModels(): array
    {
        $configured = collect(config('documents.model_groups', []))->flatten();
        $extra = Document::pluck('modelos')->flatten()
            ->map(fn ($m) => trim((string) $m))
            ->filter()
            ->reject(fn ($m) => $configured->contains(fn ($c) => mb_strtolower($c) === mb_strtolower($m)))
            ->unique(fn ($m) => mb_strtolower($m))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE);

        return $configured->merge($extra)->values()->all();
    }

    private function titleFromFileName(string $name): string
    {
        return Str::of(pathinfo($name, PATHINFO_FILENAME))->replace(['_', '-'], ' ')->squish()->ucfirst();
    }

    private function findWithFile($id): Document
    {
        $document = Document::findOrFail($id);
        abort_unless(Storage::disk(self::DISK)->exists($document->file_path), 404, __('Ficheiro não encontrado.'));

        return $document;
    }

    private function canManage(): bool
    {
        return DocumentAccess::canManage(auth()->user());
    }

    private function authorizeView(): void
    {
        abort_unless(DocumentAccess::canView(auth()->user()), 403, __('Não tem acesso à documentação.'));
    }
}
