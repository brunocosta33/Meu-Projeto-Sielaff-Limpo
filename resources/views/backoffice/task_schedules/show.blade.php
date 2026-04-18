@extends('layouts.backoffice_master')

@section('content')
<main role="main" class="main-content-wrapper">
    <div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
        <i class="fas fa-tasks fa-lg"></i>
    </div>
    <h1 class="mb-0">{{ __('Detalhes') }}</h1>
</div>

@push('styles')
@endpush

    <div class="bg-white p-3">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table">
                       <thead>
                            <tr style="font-weight: normal;">
                                <th>{{ __('Tarefa') }}</th>
                                <th>{{ __('Prioridade') }}</th>
                                <th style="text-align: center;">{{ __('Activa') }}</th>
                                <th style="text-align: center;">{{ __('Grupo') }}</th>
                                <th style="text-align: center;">{{ __('Data limite') }}</th>
                                <th style="text-align: center;">{{ __('Hora limite') }}</th>
                                <th style="text-align: center;">{{ __('Estado') }}</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: #f5f5f5;">
                            <tr>
                                <td>{{ $schedule->task->title }}</td>
                                <td>
                                    <span class="detalhe-badge prioridade-{{ strtolower($schedule->prioridade) }}">{{ $schedule->prioridade }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="detalhe-badge {{ $schedule->activa ? 'sim' : 'nao' }}">{{ $schedule->activa ? __('Sim') : __('Não') }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="detalhe-badge {{ $schedule->grupo ? 'sim' : 'nao' }}">{{ $schedule->grupo ? __('Sim') : __('Não') }}</span>
                                </td>
                                <td style="text-align: center;">{{ $schedule->display_date ? \Carbon\Carbon::parse($schedule->display_date)->format('d/m/Y') : '-' }}</td>
                                <td style="text-align: center;">{{ $schedule->display_time ? \Carbon\Carbon::parse($schedule->display_time)->format('H:i') : '-' }}</td>
                                <td style="text-align: center;">
                                    <span class="detalhe-badge estado-{{ strtolower($schedule->estado ?? 'pendente') }}">{{ $schedule->estado ?? 'Pendente' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 mt-4">
                <div class="table-responsive">
                    <table class="table">
                       <thead>
                            <tr>
                                <th>{{ __('Colaborador') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th width="60%">{{ __('Histórico Comentários') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedule->users as $user)
                                <tr style="background-color: #f6f6f6;">
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        <span class="detalhe-badge estado-{{ strtolower($user->pivot->estado ?? 'pendente') }}">
                                            {{ $user->pivot->estado ?? __('Pendente') }}
                                        </span>
                                        @if($user->pivot->data_conclusao)
                                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($user->pivot->data_conclusao)->format('d/m/Y - H:i') }}</small>
                                            @if($schedule->display_date && $user->pivot->data_conclusao > ($schedule->display_date.' '.($schedule->display_time ?? '23:59')))
                                                <span class="text-danger"> | {{ __('após limite') }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $user->pivot->comentarios }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <a class="btn btn-outline-dark rounded-pill px-4" href="{{ route('backoffice.task_schedules.index') }}">{{ __('VOLTAR') }}</a>
    </div>
</main>
@endsection
