{{--
    A lista dos processos abertos, como numa folha de Excel: uma linha por processo e,
    à frente, o ponto de situação, que se escreve ali mesmo.
    Espera: $lista, $esperas, $porVez, $filters
--}}
@php($marcas = config('maintenance.clients'))

<div class="fg-vezes mb-2">
    <span class="rotulo">{{ __('De quem é a vez') }}</span>
    <a href="{{ route('backoffice.warranty.index', \Illuminate\Support\Arr::except($filters, 'vez')) }}"
       class="fg-vez-btn {{ request('vez') ? '' : 'on' }}" style="--c: #1e293b">
        {{ __('Todos') }} <span class="n">{{ $porVez->sum() }}</span>
    </a>
    @foreach($esperas as $quem => $cfg)
        @continue($quem === 'fechados')
        <a href="{{ route('backoffice.warranty.index', ['vez' => $quem] + $filters) }}"
           class="fg-vez-btn {{ request('vez') === $quem ? 'on' : '' }}" style="--c: {{ $cfg['color'] }}">
            <i class="fas {{ $cfg['icon'] }}"></i> {{ __($cfg['curto']) }} <span class="n">{{ $porVez[$quem] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<div class="card mb-3">
    <div class="table-responsive">
        <table class="table fg-tabela mb-0">
            <thead>
                <tr>
                    <th style="width: 7.5rem">{{ __('OS') }}</th>
                    <th>{{ __('Loja') }}</th>
                    <th>{{ __('Máquina') }}</th>
                    <th style="width: 7rem">{{ __('Data da avaria') }}</th>
                    <th style="min-width: 12rem">{{ __('Avaria') }}</th>
                    <th style="min-width: 11rem">{{ __('Em que passo') }}</th>
                    <th class="th-sit" style="min-width: 18rem"><i class="fas fa-sticky-note"></i> {{ __('Ponto de situação') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($lista as $case)
                    @php($marca = $marcas[$case->cliente] ?? null)
                    @php($tipo = $case->typeConfig())
                    @php($vez = $case->waitingOnConfig())
                    @php($proximo = $case->nextStep())
                    @php($zona = $case->store?->zonaConfig())
                    @php($nota = $case->latestNote)
                    @php($abrir = route('backoffice.warranty.show', $case->id))
                    <tr>
                        <td>
                            <a href="{{ $abrir }}" class="text-decoration-none">
                                @if(filled($case->os_numero))
                                    <span class="fg-os" title="{{ __('Ordem de serviço') }}"><b>OS</b> {{ $case->os_numero }}</span>
                                @else
                                    <span class="fg-os falta">{{ __('OS por registar') }}</span>
                                @endif
                            </a>
                            <div class="fg-tipo-l" style="--tc: {{ $tipo['color'] }}; --tb: {{ $tipo['bg'] }}">
                                <i class="fas {{ $tipo['icon'] }}"></i> {{ __($tipo['curto']) }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap" style="gap: .25rem">
                                @if($marca)
                                    <span class="fg-marca" style="--grad: {{ $marca['gradiente'] }}; --txt: {{ $marca['texto'] }}">{{ $marca['label'] }}</span>
                                @endif
                                @if($zona)
                                    <span class="fg-zona" style="--zc: {{ $zona['color'] }}; --zb: {{ $zona['bg'] }}">{{ __($zona['label']) }}</span>
                                @endif
                            </div>
                            <a href="{{ $abrir }}" class="fg-loja-l">
                                <span class="cod">{{ $case->store->codigo_loja ?? '' }}</span> {{ $case->store->nome_loja ?? __('Sem loja') }}
                            </a>
                        </td>
                        <td>
                            @if($case->machine?->serial_number)
                                <span class="fg-serie"><i class="fa fa-hashtag"></i> {{ $case->machine->serial_number }}</span>
                                <div class="fg-mini">{{ $case->machine->descricao ?: $case->machine->modelo }}</div>
                            @else
                                <span class="fg-mini">—</span>
                            @endif
                        </td>
                        <td>
                            @if($case->data_avaria)
                                <div class="fg-data">{{ $case->data_avaria->format('d/m/Y') }}</div>
                                <div class="fg-mini">{{ trans_choice('{0} hoje|{1} há :count dia|[2,*] há :count dias', $d = (int) $case->data_avaria->copy()->startOfDay()->diffInDays(now()->startOfDay()), ['count' => $d]) }}</div>
                            @else
                                <span class="fg-mini">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="fg-avaria-l" title="{{ $case->descricao }}">{{ $case->descricao }}</div>
                        </td>
                        <td>
                            <div class="fg-passos" title="{{ collect($case->blocks())->map(fn ($b) => __($b['curto']))->implode(' → ') }}">
                                @foreach($case->blocks() as $chave => $bloco)
                                    <span class="{{ $case->blockState($chave, $proximo) }}" style="--bc: {{ $bloco['color'] }}" title="{{ __($bloco['label']) }}"></span>
                                @endforeach
                            </div>
                            <div class="fg-vez-l" style="--c: {{ $vez['color'] }}">
                                <i class="fas {{ $vez['icon'] }}"></i> {{ __($vez['label']) }}
                            </div>
                            <div class="fg-mini">
                                {{ __('desde') }} {{ optional($case->fase_desde)->format('d/m/Y') }}
                                · {{ trans_choice('{0} hoje|{1} :count dia|[2,*] :count dias', $case->daysInPhase(), ['count' => $case->daysInPhase()]) }}
                            </div>
                            @if($case->waitingOn() === 'nos' && $case->todoLabel())
                                <div class="fg-proximo-l">▸ {{ $case->todoLabel() }}</div>
                            @endif
                        </td>
                        <td class="td-sit">
                            <div class="fg-sit" data-url="{{ route('backoffice.warranty.notes.store', $case->id) }}">
                                <div class="ver {{ $nota ? '' : 'sem' }}">
                                    @if($nota)
                                        <div class="texto">{{ $nota->texto }}</div>
                                        <div class="quem">{{ $nota->user->name ?? '—' }} · {{ $nota->created_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div class="texto vazio">{{ __('Sem ponto de situação.') }}</div>
                                        <div class="quem"></div>
                                    @endif
                                    <button type="button" class="editar"><i class="fas fa-pen"></i> {{ $nota ? __('Atualizar') : __('Escrever') }}</button>
                                </div>
                                <div class="escrever d-none">
                                    <textarea rows="3" class="form-control form-control-sm" maxlength="2000"
                                              placeholder="{{ __('Ex.: Liguei ao cliente, vai aprovar até sexta.') }}"></textarea>
                                    <div class="d-flex align-items-center mt-1" style="gap: .35rem">
                                        <button type="button" class="btn btn-sm btn-primary gravar"><i class="fa fa-check"></i> {{ __('Gravar') }}</button>
                                        <button type="button" class="btn btn-sm btn-link text-secondary cancelar">{{ __('Cancelar') }}</button>
                                        <small class="text-muted ml-auto">Ctrl+Enter</small>
                                    </div>
                                    <div class="erro small text-danger d-none"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-right">
                            <a href="{{ $abrir }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Abrir o processo') }}"><i class="fa fa-chevron-right"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="fg-vazio"><i class="far fa-smile"></i> {{ __('Nada em aberto aqui.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var textos = { atualizar: @json(__('Atualizar')), erro: @json(__('Não foi possível gravar. Tente outra vez.')) };

        document.querySelectorAll('.fg-sit').forEach(function (caixa) {
            var ver = caixa.querySelector('.ver');
            var escrever = caixa.querySelector('.escrever');
            var campo = escrever.querySelector('textarea');
            var erro = escrever.querySelector('.erro');
            var botao = escrever.querySelector('.gravar');

            var abrir = function () {
                var atual = ver.querySelector('.texto');
                campo.value = atual.classList.contains('vazio') ? '' : atual.textContent.trim();
                ver.classList.add('d-none');
                escrever.classList.remove('d-none');
                campo.focus();
                campo.setSelectionRange(campo.value.length, campo.value.length);
            };
            var fechar = function () {
                escrever.classList.add('d-none');
                ver.classList.remove('d-none');
                erro.classList.add('d-none');
            };
            var gravar = function () {
                var texto = campo.value.trim();
                if (!texto) { campo.focus(); return; }
                botao.disabled = true;
                fetch(caixa.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ texto: texto })
                }).then(function (r) {
                    if (!r.ok) throw new Error(r.status);
                    return r.json();
                }).then(function (d) {
                    var t = ver.querySelector('.texto');
                    t.textContent = d.texto;
                    t.classList.remove('vazio');
                    ver.classList.remove('sem');
                    ver.querySelector('.quem').textContent = d.quem + ' · ' + d.quando;
                    ver.querySelector('.editar').innerHTML = '<i class="fas fa-pen"></i> ' + textos.atualizar;
                    caixa.classList.add('acabou');
                    setTimeout(function () { caixa.classList.remove('acabou'); }, 1200);
                    fechar();
                }).catch(function () {
                    erro.textContent = textos.erro;
                    erro.classList.remove('d-none');
                }).finally(function () { botao.disabled = false; });
            };

            ver.querySelector('.editar').addEventListener('click', abrir);
            ver.querySelector('.texto').addEventListener('dblclick', abrir);
            escrever.querySelector('.cancelar').addEventListener('click', fechar);
            botao.addEventListener('click', gravar);
            campo.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); gravar(); }
                if (e.key === 'Escape') fechar();
            });
        });
    });
</script>
@endpush
