@extends('layouts.backoffice_master')

@section('content')
<main role="main" class="main-content-wrapper">
    <div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
        <i class="fas fa-tasks fa-lg"></i>
    </div>
    <h1 class="mb-0">Detalhes</h1>
</div>

    <div class="bg-white p-3">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table">
                       <thead>
                            <tr style="font-weight: normal;">
                                <th style="font-weight: normal;">Tarefa</th>
                                <th style="font-weight: normal;">Prioridade</th>
                                <th style="font-weight: normal; text-align: center;">Activa</th>
                                <th style="font-weight: normal; text-align: center;">Grupo</th>
                                <th style="font-weight: normal; text-align: center;">Data limite</th>
                                <th style="font-weight: normal; text-align: center;">Hora limite</th>
                                <th style="font-weight: normal; text-align: center;">Envios</th>
                                <th style="font-weight: normal; text-align: center;">Visualizadas</th>
                                <th style="font-weight: normal; text-align: center;">Em Execução</th>
                                <th style="font-weight: normal; text-align: center;">Concluídas</th>
                                <th style="font-weight: normal; text-align: center;">%</th>
                                <th style="font-weight: normal;"></th> <!-- ícone -->
                            </tr>
                        </thead>
                        <tbody style="background-color: #f5f5f5;">
                            <tr>
                                <td>{{ $schedule->task->title }}</td>
                                <td>{{ $schedule->prioridade }}</td>
                                <td style="text-align: center;">{{ $schedule->activa ? 'Sim' : 'Não' }}</td>
                                <td style="text-align: center;">{{ $schedule->grupo ? 'Sim' : 'Não' }}</td>
                                <td style="text-align: center;">{{ $schedule->data_limite ? \Carbon\Carbon::parse($schedule->data_limite)->format('d/m/Y') : '-' }}</td>
                                <td style="text-align: center;">{{ $schedule->hora_limite ? \Carbon\Carbon::parse($schedule->hora_limite)->format('H:i') : '-' }}</td>
                                <td style="text-align: center;">{{ $totalEnvios ?? '-' }}</td>
                                <td style="text-align: center;">{{ $totalVisualizadas ?? '-' }}</td>
                                <td style="text-align: center;">{{ $totalEmExecucao ?? '-' }}</td>
                                <td style="text-align: center;">{{ $totalConcluidas ?? '-' }}</td>
                                <td style="text-align: center;">{{ $percentagemConcluida ?? '0.00' }}%</td>
                                <td style="text-align: center;">
                                    <span style="width: 18px; height: 18px; background: {{ $corEstado ?? '#ffa500' }}; border-radius: 50%; display: inline-block;"></span>
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
                                <th style="font-weight: normal;">Colaborador</th>
                                <th style="font-weight: normal;">Estado</th>
                                <th style="font-weight: normal;" width="60%">Histórico Comentários</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedule->users as $user)
                                <tr style="background-color: #f6f6f6;">
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        {{ $user->pivot->estado ?? 'Pendente' }}
                                        @if($user->pivot->data_conclusao)
                                            | {{ \Carbon\Carbon::parse($user->pivot->data_conclusao)->format('d/m/Y - H:i') }}
                                            @if($schedule->data_limite && $user->pivot->data_conclusao > $schedule->data_limite.' '.$schedule->hora_limite)
                                                <span class="text-danger"> | após limite</span>
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

        <a class="btn btn-outline-dark rounded-pill px-4" href="{{ route('backoffice.task_schedules.index') }}">VOLTAR</a>
    </div>
</main>
@endsection
