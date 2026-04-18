<script>
@php
    $storeMachinesMap = $stores->mapWithKeys(function ($store) {
        return [
            $store->id => $store->machines->map(function ($machine) {
                return [
                    'id' => $machine->id,
                    'serial_number' => $machine->serial_number,
                    'ip_address' => $machine->ip_address,
                    'descricao' => $machine->descricao,
                ];
            })->values()->all(),
        ];
    });
@endphp

document.addEventListener('DOMContentLoaded', function () {
    var estadoSelect = document.getElementById('estado');
    var dataPedidoInput = document.getElementById('data_pedido');
    var dataAgendamentoGroup = document.getElementById('data_agendamento_group');
    var dataAgendamentoInput = document.getElementById('data_agendamento');
    var dataResolucaoGroup = document.getElementById('data_resolucao_group');
    var dataResolucaoInput = document.getElementById('data_resolucao');
    var storeSelect = document.getElementById('store_id');
    var machineSelect = document.getElementById('machine_id');
    var storeSummary = document.getElementById('store_summary');
    var storeSummaryInsignia = document.getElementById('store_summary_insignia');
    var storeSummaryRegiao = document.getElementById('store_summary_regiao');
    var storeSummaryCidade = document.getElementById('store_summary_cidade');
    var storeSummaryMorada = document.getElementById('store_summary_morada');
    var storeSummaryContacto = document.getElementById('store_summary_contacto');
    var storeSummaryTelefone = document.getElementById('store_summary_telefone');
    var storeSummaryEmail = document.getElementById('store_summary_email');
    var initialState = estadoSelect ? estadoSelect.value : '';
    var emptyMachineLabel = @json(__('-- Sem máquina associada --'));
    var storesMachines = @json($storeMachinesMap);

    if (window.jQuery && $('.selectpicker').length) {
        $('.selectpicker').selectpicker();
    }

    if (machineSelect) {
        machineSelect.dataset.selectedMachine = machineSelect.value || machineSelect.dataset.selectedMachine || '';
    }

    function syncFields(clearHiddenValues) {
        if (!estadoSelect) {
            return;
        }

        var currentState = estadoSelect.value;
        var isScheduled = currentState === 'agendado';
        var isResolved = currentState === 'concluido';

        if (dataAgendamentoGroup) {
            dataAgendamentoGroup.style.display = isScheduled ? 'block' : 'none';
        }

        if (dataAgendamentoInput && !isScheduled && clearHiddenValues) {
            dataAgendamentoInput.value = '';
        }

        if (dataResolucaoInput) {
            dataResolucaoInput.disabled = !isResolved;
        }

        if (dataResolucaoGroup) {
            dataResolucaoGroup.style.opacity = isResolved ? '1' : '0.65';
        }

        if (dataResolucaoInput && !isResolved && clearHiddenValues && currentState !== initialState) {
            dataResolucaoInput.value = '';
        }
    }

    function syncResolutionMinDate() {
        if (!dataPedidoInput || !dataResolucaoInput || !dataPedidoInput.value) {
            return;
        }

        dataResolucaoInput.min = dataPedidoInput.value + 'T00:00';

        if (dataResolucaoInput.value && dataResolucaoInput.value < dataResolucaoInput.min) {
            dataResolucaoInput.value = dataResolucaoInput.min;
        }
    }

    function syncStoreSummary() {
        if (!storeSelect || !storeSummary) {
            return;
        }

        var selectedOption = storeSelect.options[storeSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            storeSummary.style.display = 'none';
            return;
        }

        storeSummary.style.display = 'block';
        storeSummaryInsignia.textContent = selectedOption.dataset.insignia || '-';
        storeSummaryRegiao.textContent = selectedOption.dataset.regiao || '-';
        storeSummaryCidade.textContent = selectedOption.dataset.cidade || '-';
        storeSummaryMorada.textContent = selectedOption.dataset.morada || '-';
        storeSummaryContacto.textContent = selectedOption.dataset.contacto || '-';
        storeSummaryTelefone.textContent = selectedOption.dataset.telefone || '-';
        storeSummaryEmail.textContent = selectedOption.dataset.email || '-';
        storeSummaryInsignia.className = 'badge ' + ((selectedOption.dataset.insignia || '').toLowerCase() === 'lidl' ? 'badge-warning' : 'badge-success');
    }

    function syncMachineOptions() {
        if (!storeSelect || !machineSelect) {
            return;
        }

        var storeId = storeSelect.value;
        var selectedMachineId = machineSelect.dataset.selectedMachine || machineSelect.value || '';
        var machines = storesMachines[storeId] || [];
        var hasSelectedMachine = false;

        machineSelect.innerHTML = '<option value="">' + emptyMachineLabel + '</option>';

        machines.forEach(function (machine) {
            var option = document.createElement('option');
            option.value = machine.id;
            option.textContent = machine.serial_number + (machine.descricao ? ' - ' + machine.descricao : '');

            if (String(selectedMachineId) === String(machine.id)) {
                option.selected = true;
                hasSelectedMachine = true;
            }

            machineSelect.appendChild(option);
        });

        machineSelect.value = hasSelectedMachine ? String(selectedMachineId) : '';

        if (window.jQuery && $(machineSelect).hasClass('selectpicker')) {
            $(machineSelect).selectpicker('refresh');
            $(machineSelect).selectpicker('val', machineSelect.value);
        }
    }

    if (estadoSelect) {
        estadoSelect.addEventListener('change', function () {
            syncFields(true);
        });
    }

    if (dataPedidoInput) {
        dataPedidoInput.addEventListener('change', function () {
            syncResolutionMinDate();
        });
    }

    if (storeSelect) {
        storeSelect.addEventListener('change', function () {
            machineSelect.dataset.selectedMachine = '';
            syncStoreSummary();
            syncMachineOptions();
        });
    }

    if (machineSelect) {
        machineSelect.addEventListener('change', function () {
            machineSelect.dataset.selectedMachine = machineSelect.value || '';
        });
    }

    syncFields(false);
    syncResolutionMinDate();
    syncStoreSummary();
});
</script>
