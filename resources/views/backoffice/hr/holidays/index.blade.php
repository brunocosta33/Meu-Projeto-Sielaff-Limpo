@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale('pt'))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Feriados') }} {{ $year }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body d-flex align-items-center">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.hr.holidays.index', ['ano' => $year - 1]) }}"><i class="fa fa-chevron-left"></i></a>
                <h4 class="mb-0 mx-3">{{ __('Feriados') }} {{ $year }}</h4>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('backoffice.hr.holidays.index', ['ano' => $year + 1]) }}"><i class="fa fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-flag text-danger"></i> {{ __('Feriados nacionais') }}</h5>
                <p class="small text-muted">{{ __('Calculados automaticamente todos os anos (incluindo Páscoa e Corpo de Deus). Não contam como dias de férias.') }}</p>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach($national as $date => $name)
                            @php($d = \Carbon\Carbon::parse($date))
                            <tr>
                                <td class="text-nowrap" style="width: 160px">{{ ucfirst($d->translatedFormat('D, d M')) }}</td>
                                <td>{{ $name }}</td>
                                <td class="text-right">@if($d->isWeekend())<span class="badge badge-light">{{ __('fim de semana') }}</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-building" style="color:#ad1457"></i> {{ __('Feriados da empresa') }}</h5>
                @if($custom->isEmpty())
                    <p class="text-muted mb-0">{{ __('Ainda não há feriados próprios registados para este ano.') }}</p>
                @else
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($custom as $h)
                                @php($d = $h->recorrente ? \Carbon\Carbon::create($year, $h->data->month, $h->data->day) : $h->data)
                                <tr>
                                    <td class="text-nowrap" style="width: 160px">{{ ucfirst($d->translatedFormat('D, d M')) }}</td>
                                    <td>
                                        {{ $h->nome }}
                                        @if($h->recorrente)<span class="badge badge-info ml-1">{{ __('todos os anos') }}</span>@endif
                                    </td>
                                    <td class="small text-muted">{{ $h->creator->name ?? '' }}</td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('backoffice.hr.holidays.destroy', $h->id) }}" class="d-inline"
                                              onsubmit="return confirm('Remover este feriado?{{ $h->recorrente ? ' Será removido de todos os anos.' : '' }} As férias marcadas serão recalculadas.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link btn-sm text-danger p-0"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title"><i class="fa fa-plus text-primary"></i> {{ __('Adicionar feriado') }}</h5>
                <p class="small text-muted">{{ __('Ex.: feriado municipal, ponte ou dia dado pela empresa. Esse dia deixa de contar como férias e as férias já marcadas são recalculadas.') }}</p>
                <form method="POST" action="{{ route('backoffice.hr.holidays.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Dia') }}</label>
                        <input type="date" name="data" class="form-control" value="{{ old('data') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="small mb-1">{{ __('Nome') }}</label>
                        <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" placeholder="{{ __('Ex.: Feriado municipal') }}" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" name="recorrente" value="1" id="recorrente" @checked(old('recorrente'))>
                        <label class="form-check-label" for="recorrente">{{ __('Repete todos os anos neste dia') }}</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
