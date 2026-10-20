@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($filters = request()->only(['q', 'estado']))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Manutenções Anuais') }} {{ $year }}</title>
@endsection

@push('styles')
<style>
    .mt-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem; }
    .mt-stat { display: block; border-radius: 14px; padding: .8rem 1rem; background: var(--bg); color: var(--fg); border: 2px solid transparent;
               text-decoration: none !important; transition: transform .15s; }
    .mt-stat:hover { transform: translateY(-2px); color: var(--fg); }
    .mt-stat.is-active { border-color: var(--fg); }
    .mt-stat .num { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .mt-stat .lbl { font-size: .8rem; font-weight: 600; margin-top: .25rem; }
    .mt-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .78rem; font-weight: 600; padding: .2rem .55rem;
                border-radius: 999px; background: var(--bg); color: var(--fg); border: 1px solid var(--fg); white-space: nowrap; }
    .mt-table td { vertical-align: middle; }
    .mt-store { font-weight: 600; }
    .mt-store small { display: block; font-weight: 400; color: #6c757d; }
    .mt-window { white-space: nowrap; font-size: .85rem; }
    .mt-window .due { font-weight: 700; font-size: .95rem; }
    .mt-done-date { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .7rem; border-radius: 9px; background: #dcfce7; color: #166534;
                    border-left: 4px solid #16a34a; font-weight: 800; font-size: .95rem; white-space: nowrap; }
    .mt-doc { display: inline-flex; align-items: center; gap: .3rem; margin-top: .3rem; font-weight: 600; font-size: .78rem; color: #1565c0; background: #e3f0ff; border-radius: 999px; padding: .1rem .55rem; text-decoration: none !important; }
    .mt-doc:hover { background: #cfe3ff; }
    .mt-notes { font-size: .84rem; color: #334155; max-width: 280px; white-space: pre-line; overflow-wrap: anywhere; }
    .mt-doc-missing { display: inline-block; margin-top: .3rem; font-size: .74rem; color: #8a96a8; }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: .75rem">
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.maintenances.index', ['ano' => $year - 1] + $filters) }}"><i class="fa fa-chevron-left"></i></a>
                <h4 class="mb-0 mx-3"><i class="fas fa-clipboard-check text-primary"></i> {{ __('Manutenções Anuais') }} {{ $year }}</h4>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.maintenances.index', ['ano' => $year + 1] + $filters) }}"><i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                <form method="GET" class="form-inline">
                    <input type="hidden" name="ano" value="{{ $year }}">
                    @if(request('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('Pesquisar loja, código, insígnia…') }}" style="min-width: 240px">
                </form>
                <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse" data-target="#mt-new"><i class="fa fa-plus"></i> {{ __('Adicionar loja') }}</button>
            </div>
        </div>

        <div class="mt-summary">
            <a href="{{ route('backoffice.maintenances.index', ['ano' => $year] + \Illuminate\Support\Arr::except($filters, 'estado')) }}"
               class="mt-stat {{ request('estado') ? '' : 'is-active' }}" style="--bg: #f1f3f6; --fg: #263238">
                <div class="num">{{ $total }}</div><div class="lbl">{{ __('Total de lojas') }}</div>
            </a>
            @foreach(['atrasada', 'janela', 'proxima', 'futura', 'feita'] as $key)
                @php($state = $states[$key])
                @php($n = $key === 'feita' ? ($counts['feita'] ?? 0) + ($counts['feita_fora'] ?? 0) : ($counts[$key] ?? 0))
                <a href="{{ route('backoffice.maintenances.index', ['ano' => $year, 'estado' => $key] + $filters) }}"
                   class="mt-stat {{ request('estado') === $key ? 'is-active' : '' }}" style="--bg: {{ $state['bg'] }}; --fg: {{ $state['color'] }}">
                    <div class="num">{{ $n }}</div><div class="lbl"><i class="fas {{ $state['icon'] }}"></i> {{ $key === 'feita' ? __('Feitas') : __($state['label']) }}</div>
                </a>
            @endforeach
        </div>
        <p class="small text-muted mb-0 mt-2">
            <i class="fa fa-info-circle"></i> {{ __('A manutenção pode ser feita entre :dias dias úteis antes e :dias dias úteis depois da data contratada, que se repete todos os anos. Contam de segunda a sábado; domingos e feriados não contam.', ['dias' => $windowDays]) }}
        </p>
    </div>
</div>

{{-- Nova loja com manutenção --}}
<div class="collapse {{ $errors->has('store_id') ? 'show' : '' }}" id="mt-new">
    <div class="card mb-3 border-primary">
        <div class="card-body">
            <h5 class="card-title"><i class="fa fa-plus text-primary"></i> {{ __('Adicionar loja com manutenção anual') }}</h5>
            <form method="POST" action="{{ route('backoffice.maintenances.store') }}" class="form-row align-items-end">
                @csrf
                <div class="form-group col-md-5">
                    <label class="small mb-1">{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Escolha a loja…') }}" required>
                        @foreach($storesWithout as $s)
                            <option value="{{ $s->id }}" @selected(old('store_id') == $s->id)>{{ $s->codigo_loja }} — {{ $s->nome_loja }} ({{ $s->insignia }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="small mb-1">{{ __('Data contratada') }}</label>
                    <input type="date" name="data_contratada" class="form-control" value="{{ old('data_contratada') }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label class="small mb-1">{{ __('Observações') }}</label>
                    <input type="text" name="observacoes" class="form-control" value="{{ old('observacoes') }}">
                </div>
                <div class="form-group col-md-1">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Lista --}}
<div class="card">
    <div class="card-body">
        @if($rows->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <p class="mb-0">{{ $total ? __('Nenhuma loja corresponde aos filtros.') : __('Ainda não há lojas com manutenção anual registada para este ano.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table mt-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Loja') }}</th>
                            <th>{{ __('Data contratada / prazo') }} {{ $year }}</th>
                            <th>{{ __('Estado') }}</th>
                            <th>{{ __('Feita em') }}</th>
                            <th>{{ __('Observações') }}</th>
                            <th class="text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php($state = $states[$row->status])
                            <tr>
                                <td class="mt-store">
                                    {{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}
                                    <small>{{ $row->store->insignia }}{{ $row->store->cidade ? ' · ' . $row->store->cidade : '' }}</small>
                                </td>
                                <td class="mt-window">
                                    <span class="due">{{ $row->due->format('d/m/Y') }}</span>
                                    <div class="text-muted">{{ __('de') }} {{ $row->start->format('d/m') }} {{ __('a') }} {{ $row->end->format('d/m') }}</div>
                                </td>
                                <td>
                                    <span class="mt-badge" style="--bg: {{ $state['bg'] }}; --fg: {{ $state['color'] }}"><i class="fas {{ $state['icon'] }}"></i> {{ __($state['label']) }}</span>
                                    @if($row->status === 'atrasada')
                                        <div class="small text-danger mt-1">{{ $row->end->diffInDays(now()->startOfDay()) }} {{ __('dias de atraso') }}</div>
                                    @elseif($row->status === 'janela')
                                        <div class="small mt-1" style="color: {{ $state['color'] }}">{{ trans_choice('{0} hoje é o último dia do prazo|{1} falta :count dia útil para o fim do prazo|[2,*] faltam :count dias úteis para o fim do prazo', $n = \App\Models\StoreMaintenance::workingDaysBetween(now()->startOfDay(), $row->end), ['count' => $n]) }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($row->record)
                                        <span class="mt-done-date"><i class="fa fa-check-circle"></i> {{ $row->record->data_realizada->format('d/m/Y') }}</span>
                                        @if($row->record->technician)<div>{{ $row->record->technician->name }}</div>@endif
                                        @if($row->record->observacoes)<div class="text-muted">{{ \Illuminate\Support\Str::limit($row->record->observacoes, 60) }}</div>@endif
                                        @if($row->record->documento_path)
                                            <a href="{{ route('backoffice.maintenances.document', $row->record->id) }}" class="mt-doc" title="{{ $row->record->documento_nome }}">
                                                <i class="fa fa-file-alt"></i> {{ __('Folha de obra') }}
                                            </a>
                                        @else
                                            <span class="mt-doc-missing"><i class="fa fa-paperclip"></i> {{ __('sem folha de obra') }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="mt-notes">
                                    @if($row->maintenance->observacoes)
                                        {{ $row->maintenance->observacoes }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right text-nowrap">
                                    @if($row->record)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#mt-attach"
                                                title="{{ $row->record->documento_path ? __('Substituir folha de obra') : __('Anexar folha de obra') }}"
                                                data-action="{{ route('backoffice.maintenances.document.store', $row->record->id) }}"
                                                data-delete="{{ $row->record->documento_path ? route('backoffice.maintenances.document.delete', $row->record->id) : '' }}"
                                                data-current="{{ $row->record->documento_nome }}"
                                                data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}">
                                            <i class="fa fa-paperclip"></i>
                                        </button>
                                        <form method="POST" action="{{ route('backoffice.maintenances.undo', $row->record->id) }}" class="d-inline"
                                              onsubmit="return confirm({{ json_encode(__('Anular o registo desta manutenção? Volta a ficar por fazer (a folha de obra anexada também é apagada).')) }})">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary" title="{{ __('Anular registo') }}"><i class="fa fa-undo"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#mt-done"
                                                data-action="{{ route('backoffice.maintenances.done', $row->maintenance->id) }}"
                                                data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}"
                                                data-window="{{ $row->start->format('d/m/Y') }} {{ __('a') }} {{ $row->end->format('d/m/Y') }}">
                                            <i class="fa fa-check"></i> {{ __('Marcar feita') }}
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-link text-secondary px-1" title="{{ __('Editar') }}" data-toggle="modal" data-target="#mt-edit"
                                            data-action="{{ route('backoffice.maintenances.update', $row->maintenance->id) }}"
                                            data-delete="{{ route('backoffice.maintenances.destroy', $row->maintenance->id) }}"
                                            data-store="{{ $row->store->codigo_loja }} — {{ $row->store->nome_loja }}"
                                            data-date="{{ $row->maintenance->data_contratada->toDateString() }}"
                                            data-notes="{{ $row->maintenance->observacoes }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal: marcar como feita --}}
<div class="modal fade" id="mt-done" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content" id="mt-done-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ano" value="{{ $year }}">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-check-circle text-success"></i> {{ __('Manutenção feita') }} {{ $year }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-1 font-weight-bold" id="mt-done-store"></p>
                <p class="small text-muted">{{ __('Prazo') }}: <span id="mt-done-window"></span></p>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Data em que foi feita') }}</label>
                    <input type="date" name="data_realizada" class="form-control" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Técnico') }}</label>
                    <select name="user_id" class="form-control">
                        <option value="">—</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="small mb-1"><i class="fa fa-file-alt"></i> {{ __('Folha de obra (opcional)') }}</label>
                    <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx">
                    <small class="form-text text-muted">{{ __('PDF, foto ou Word, até 32 MB. Pode ser anexada mais tarde.') }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('Marcar como feita') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: anexar / substituir folha de obra --}}
<div class="modal fade" id="mt-attach" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="mt-attach-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-file-alt text-primary"></i> {{ __('Folha de obra') }} {{ $year }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold" id="mt-attach-store"></p>
                    <p class="small text-muted" id="mt-attach-current"></p>
                    <div class="form-group mb-0">
                        <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx" required>
                        <small class="form-text text-muted">{{ __('PDF, foto ou Word, até 32 MB.') }}</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" form="mt-attach-delete" class="btn btn-link text-danger" id="mt-attach-delete-btn" onclick="return confirm({{ json_encode(__('Remover a folha de obra?')) }})">
                        <i class="fa fa-trash"></i> {{ __('Remover') }}
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ __('Gravar') }}</button>
                    </div>
                </div>
            </form>
            <form method="POST" id="mt-attach-delete">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

{{-- Modal: editar contrato --}}
<div class="modal fade" id="mt-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="mt-edit-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> {{ __('Editar manutenção anual') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold" id="mt-edit-store"></p>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Data contratada') }}</label>
                        <input type="date" name="data_contratada" id="mt-edit-date" class="form-control" required>
                        <small class="form-text text-muted">{{ __('Repete-se todos os anos no mesmo dia e mês.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Observações') }}</label>
                        <textarea name="observacoes" id="mt-edit-notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" form="mt-delete-form" class="btn btn-link text-danger" onclick="return confirm({{ json_encode(__('Remover esta loja das manutenções anuais? O histórico também é apagado.')) }})">
                        <i class="fa fa-trash"></i> {{ __('Remover') }}
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                    </div>
                </div>
            </form>
            <form method="POST" id="mt-delete-form">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery) return;
        $('#mt-done').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-done-form').attr('action', b.data('action'));
            $('#mt-done-store').text(b.data('store'));
            $('#mt-done-window').text(b.data('window'));
        });
        $('#mt-attach').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-attach-form').attr('action', b.data('action'));
            $('#mt-attach-store').text(b.data('store'));
            var current = b.data('current');
            $('#mt-attach-current').text(current ? @json(__('Atual: :nome (será substituída)')).replace(':nome', current) : @json(__('Ainda sem folha de obra anexada.')));
            $('#mt-attach-delete').attr('action', b.data('delete') || '');
            $('#mt-attach-delete-btn').toggle(!!b.data('delete'));
        });
        $('#mt-edit').on('show.bs.modal', function (e) {
            var b = $(e.relatedTarget);
            $('#mt-edit-form').attr('action', b.data('action'));
            $('#mt-delete-form').attr('action', b.data('delete'));
            $('#mt-edit-store').text(b.data('store'));
            $('#mt-edit-date').val(b.data('date'));
            $('#mt-edit-notes').val(b.data('notes') || '');
        });
        if ($('.selectpicker').length && $.fn.selectpicker) $('.selectpicker').selectpicker();
    });
</script>
@endpush
