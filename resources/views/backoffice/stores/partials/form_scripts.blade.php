<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrapper = document.getElementById('machines-wrapper');
    var addButton = document.getElementById('add-machine-row');

    if (!wrapper || !addButton) {
        return;
    }

    function nextIndex() {
        var rows = wrapper.querySelectorAll('.machine-row');
        if (!rows.length) {
            return 0;
        }

        return Math.max.apply(null, Array.from(rows).map(function (row) {
            return parseInt(row.dataset.index || '0', 10);
        })) + 1;
    }

    function buildRow(index) {
        var row = document.createElement('div');
        row.className = 'machine-row border rounded p-3 mb-3';
        row.dataset.index = index;
        row.innerHTML = '' +
            '<input type="hidden" name="machines[' + index + '][id]" value="">' +
            '<div class="row align-items-end">' +
                '<div class="col-md-4">' +
                    '<div class="form-group mb-3">' +
                        '<label>Número de Série</label>' +
                        '<input type="text" name="machines[' + index + '][serial_number]" class="form-control" placeholder="Obrigatório se existir máquina">' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3">' +
                    '<div class="form-group mb-3">' +
                        '<label>IP</label>' +
                        '<input type="text" name="machines[' + index + '][ip_address]" class="form-control" placeholder="192.168.1.100">' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<div class="form-group mb-3">' +
                        '<label>Modelo / Descrição</label>' +
                        '<input type="text" name="machines[' + index + '][descricao]" class="form-control" placeholder="Ex: Glory, recycler, kiosk...">' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-1">' +
                    '<div class="form-group mb-3 text-right">' +
                        '<button type="button" class="btn btn-outline-danger remove-machine-row">' +
                            '<i class="fa fa-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        return row;
    }

    addButton.addEventListener('click', function () {
        wrapper.appendChild(buildRow(nextIndex()));
    });

    wrapper.addEventListener('click', function (event) {
        var button = event.target.closest('.remove-machine-row');
        if (!button) {
            return;
        }

        var rows = wrapper.querySelectorAll('.machine-row');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input').forEach(function (input) {
                input.value = '';
            });
            return;
        }

        button.closest('.machine-row').remove();
    });
});
</script>
