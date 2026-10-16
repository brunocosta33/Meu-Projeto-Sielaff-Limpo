@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Colaboradores') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.hr.staff.create') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-plus"></i> {{ __('Novo Colaborador') }}
                    </a>
                </div>

                <h5 class="card-title">{{ __('Colaboradores') }}</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Nome') }}</th>
                                <th>{{ __('Função') }}</th>
                                <th>{{ __('Escala (grupo · zona)') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $member)
                                <tr>
                                    <td>{{ $member->user->name ?? '—' }}</td>
                                    <td>
                                        @if($member->funcao)<span class="badge text-white" style="background: {{ $member->funcao_color }}; font-size: .85rem">{{ __($member->funcao_label) }}</span>@else — @endif
                                    </td>
                                    <td>{{ $member->rotation_group ? __('Grupo') . ' ' . $member->rotation_group . ($member->zona ? ' · ' . __(config('hr.zones.' . $member->zona)) : '') : '—' }}</td>
                                    <td>
                                        @if($member->ativo)
                                            <span class="badge badge-success">{{ __('Ativo') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('Inativo') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('backoffice.hr.staff.edit', $member->id) }}" class="ml-2"><i class="fa fa-edit" title="{{ __('Editar') }}"></i></a>
                                        <form action="{{ route('backoffice.hr.staff.delete', $member->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm({{ json_encode(__('Tem a certeza que deseja remover este colaborador? As substituições associadas também serão apagadas.')) }})">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0 ml-2 text-danger"><i class="fa fa-trash" title="{{ __('Remover') }}"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">{{ __('Nenhum colaborador registado.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
