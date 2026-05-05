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
    $openRequestsByStoreMap = $openRequestsByStore ?? collect();
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
    var machineModelDisplay = document.getElementById('machine_model_display');
    var filesInput = document.getElementById('files');
    var filesUploadStatus = document.getElementById('files_upload_status');
    var storeSummary = document.getElementById('store_summary');
    var storeSummaryInsignia = document.getElementById('store_summary_insignia');
    var storeSummaryRegiao = document.getElementById('store_summary_regiao');
    var storeSummaryCidade = document.getElementById('store_summary_cidade');
    var storeSummaryMorada = document.getElementById('store_summary_morada');
    var storeSummaryContacto = document.getElementById('store_summary_contacto');
    var storeSummaryTelefone = document.getElementById('store_summary_telefone');
    var storeSummaryEmail = document.getElementById('store_summary_email');
    var openRequestsWarning = document.getElementById('open_requests_warning');
    var initialState = estadoSelect ? estadoSelect.value : '';
    var emptyMachineLabel = @json(__('-- Sem máquina associada --'));
    var storesMachines = @json($storeMachinesMap);
    var openRequestsByStore = @json($openRequestsByStoreMap);
    var storeSelectionTouched = false;

    function selectedStoreId() {
        if (!storeSelect) {
            return '';
        }

        if (window.jQuery && $(storeSelect).hasClass('selectpicker')) {
            return String($(storeSelect).selectpicker('val') || storeSelect.value || '');
        }

        return String(storeSelect.value || '');
    }

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

        if (dataResolucaoInput && isResolved) {
            syncResolutionMinDate();
        }

    }

    function syncResolutionMinDate() {
        if (!dataResolucaoInput) {
            return;
        }

        var minDate = dataPedidoInput && dataPedidoInput.value
            ? dataPedidoInput.value
            : dataResolucaoInput.dataset.minDate;

        if (!minDate) {
            return;
        }

        dataResolucaoInput.min = minDate + 'T00:00';

        if (!dataResolucaoInput.disabled && !dataResolucaoInput.value) {
            dataResolucaoInput.value = dataResolucaoInput.min;
            return;
        }

        if (dataResolucaoInput.value && dataResolucaoInput.value < dataResolucaoInput.min) {
            dataResolucaoInput.value = dataResolucaoInput.min;
            dataResolucaoInput.setCustomValidity('');
            return;
        }

        dataResolucaoInput.setCustomValidity('');
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

        var storeId = selectedStoreId();
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

        syncMachineModel();
    }

    function selectedMachineData() {
        if (!machineSelect) {
            return null;
        }

        var storeId = selectedStoreId();
        var machineId = machineSelect.value || '';
        var machines = storesMachines[storeId] || [];

        return machines.find(function (machine) {
            return String(machine.id) === String(machineId);
        }) || null;
    }

    function syncMachineModel() {
        if (!machineModelDisplay) {
            return;
        }

        var machine = selectedMachineData();
        machineModelDisplay.textContent = machine && machine.descricao ? machine.descricao : '—';
    }

    function renderOpenRequestsWarning() {
        if (!storeSelect || !openRequestsWarning) {
            return;
        }

        var storeId = selectedStoreId();
        var requests = openRequestsByStore[storeId] || [];

        openRequestsWarning.style.display = storeSelectionTouched && storeId && requests.length ? 'block' : 'none';
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

    if (dataResolucaoInput) {
        dataResolucaoInput.addEventListener('change', function () {
            syncResolutionMinDate();
        });

        dataResolucaoInput.addEventListener('input', function () {
            syncResolutionMinDate();
        });

        dataResolucaoInput.addEventListener('focus', function () {
            syncResolutionMinDate();
        });

        dataResolucaoInput.addEventListener('click', function () {
            syncResolutionMinDate();
        });

        dataResolucaoInput.addEventListener('blur', function () {
            syncResolutionMinDate();
        });
    }

    function handleStoreChange(markAsTouched) {
        if (!storeSelect) {
            return;
        }

        if (machineSelect) {
            machineSelect.dataset.selectedMachine = '';
        }

        syncStoreSummary();
        syncMachineOptions();
        if (markAsTouched) {
            storeSelectionTouched = true;
        }
        renderOpenRequestsWarning();
        setTimeout(renderOpenRequestsWarning, 0);
    }

    if (storeSelect) {
        if (window.jQuery && $(storeSelect).hasClass('selectpicker')) {
            $(storeSelect).on('changed.bs.select', function (event, clickedIndex) {
                handleStoreChange(clickedIndex !== null && typeof clickedIndex !== 'undefined');
            });
        } else {
            storeSelect.addEventListener('change', function () {
                handleStoreChange(true);
            });
        }
    }

    if (machineSelect) {
        machineSelect.addEventListener('change', function () {
            machineSelect.dataset.selectedMachine = machineSelect.value || '';
            syncMachineModel();
        });
    }

    function fileToImage(file) {
        return new Promise(function (resolve, reject) {
            var image = new Image();
            var objectUrl = URL.createObjectURL(file);

            image.onload = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };

            image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Invalid image'));
            };

            image.src = objectUrl;
        });
    }

    function canvasToBlob(canvas, type, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(resolve, type, quality);
        });
    }

    async function compressImage(file) {
        if (!file.type || !file.type.match(/^image\//) || file.type === 'image/gif' || file.size <= 1200 * 1024) {
            return file;
        }

        var image = await fileToImage(file);
        var maxSide = 1600;
        var scale = Math.min(1, maxSide / Math.max(image.width, image.height));
        var canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.width * scale));
        canvas.height = Math.max(1, Math.round(image.height * scale));

        var context = canvas.getContext('2d');
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        var blob = await canvasToBlob(canvas, 'image/jpeg', 0.78);

        if (!blob || blob.size >= file.size) {
            return file;
        }

        var fileName = file.name.replace(/\.[^.]+$/, '') + '.jpg';

        return new File([blob], fileName, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
    }

    async function compressSelectedImages() {
        if (!filesInput || !filesInput.files || !filesInput.files.length || typeof DataTransfer === 'undefined') {
            return;
        }

        var originalFiles = Array.from(filesInput.files);
        var compressedFiles = [];
        var changedFiles = 0;

        if (filesUploadStatus) {
            filesUploadStatus.textContent = @json(__('A preparar imagens para envio...'));
        }

        for (var index = 0; index < originalFiles.length; index++) {
            var originalFile = originalFiles[index];
            var compressedFile = await compressImage(originalFile);

            if (compressedFile.size < originalFile.size) {
                changedFiles++;
            }

            compressedFiles.push(compressedFile);
        }

        if (!changedFiles) {
            if (filesUploadStatus) {
                filesUploadStatus.textContent = '';
            }
            return;
        }

        var dataTransfer = new DataTransfer();
        compressedFiles.forEach(function (file) {
            dataTransfer.items.add(file);
        });

        filesInput.files = dataTransfer.files;

        if (filesUploadStatus) {
            filesUploadStatus.textContent = @json(__('Imagens otimizadas para envio.'));
        }
    }

    if (filesInput) {
        var requestForm = filesInput.closest('form');

        if (requestForm) {
            requestForm.addEventListener('submit', async function (event) {
                if (requestForm.dataset.filesPrepared === '1') {
                    return;
                }

                event.preventDefault();
                try {
                    await compressSelectedImages();
                } catch (error) {
                    if (filesUploadStatus) {
                        filesUploadStatus.textContent = '';
                    }
                }
                requestForm.dataset.filesPrepared = '1';
                requestForm.submit();
            });
        }
    }

    syncFields(false);
    syncResolutionMinDate();
    syncStoreSummary();
    if (openRequestsWarning) {
        openRequestsWarning.style.display = 'none';
    }
});
</script>
