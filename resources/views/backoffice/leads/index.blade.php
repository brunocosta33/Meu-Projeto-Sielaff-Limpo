@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ str_replace('.', ' ', config('app.name')) }} - {{ __('Leads') }}</title>
@endsection

@section('head-script')
@endsection

@section('content')
    <div class="row">
        @include('flash::message')
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="float-right">
                        <!-- <a href="{{ route('backoffice.leads.create') }}" class="collapsed" data-parent="#sidebar">
                            <button type="submit" class="btn btn-outline-secondary" title={{ __('Create') }}><span
                                    class="fa fa-plus"></span>&nbsp;&nbsp;{{ __('Create') }}</button>
                        </a> -->
                    </div>
                    <h5 class="card-title">{{ __('Leads') }}</h5>
                    <br>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Received') }}</th>
                                    <th scope="col">{{ __('State') }}</th>
                                    <th scope="col"></th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr scope="row">
                                        <td>{{ $lead->id }}</td>
                                        <td>{{ $lead->name }}</td>
                                        <td>{{date('d-m-Y', strtotime($lead->created_at))}}</td>
                                        <td>{{ $lead->state->name }}</td>


                                        <td class="text-right">
                                            <a class="ml-2" href="{{ route('backoffice.leads.show', $lead->id) }}"><i
                                                    class="fa fa-eye mr-1" title={{ __('Show') }}></i></a>
                                        </td>
                                        @if (auth()->user()->role()->first()->name == 'admin')
                                            <td>
                                                <a class="ml-2"
                                                    href="{{ route('backoffice.leads.delete', $lead->id) }}"
                                                    onclick="return confirm('{{ __('Are you sure?') }}')"><i
                                                        class="fa fa-trash mr-1" title={{ __('Delete') }}></i></a>
                                            </td>
                                        @else
                                            <td>
                                                <a href=""><i class="text-secondary ml-2 fa fa-trash mr-1"
                                                        title={{ __('Unavailable') }}></i></a>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <td>{{ __('No registered Leads') }}</td>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('foot-scripts')
@endsection
