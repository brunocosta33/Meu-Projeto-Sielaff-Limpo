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
                        '<label>' + @json(__('Número de Série')) + '</label>' +
                        '<input type="text" name="machines[' + index + '][serial_number]" class="form-control" placeholder="' + @json(e(__('Obrigatório se existir máquina'))) + '">' +
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
                        '<label>' + @json(__('Modelo / Descrição')) + '</label>' +
                        '<input type="text" name="machines[' + index + '][descricao]" class="form-control" placeholder="' + {{ \Illuminate\Support\Js::from(e(__('Ex: Glory, recycler, kiosk...'))) }} + '">' +
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

<style>
    .store-files-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .75rem; }
    .store-file-item { display: flex; flex-direction: column; gap: .3rem; margin: 0; font-size: .78rem; cursor: default; }
    .store-file-thumb { display: grid; place-items: center; aspect-ratio: 4 / 3; border-radius: 10px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; }
    .store-file-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .store-file-thumb .fa-file-pdf { font-size: 2.2rem; color: #dc2626; }
    .store-file-name { color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .store-file-remove { color: #b91c1c; cursor: pointer; }
    .store-file-drop { display: flex; align-items: center; gap: .85rem; padding: .9rem 1rem; border: 2px dashed #cbd5e1; border-radius: 12px; cursor: pointer;
                       background: #f8fafc; color: #334155; margin: 0; }
    .store-file-drop:hover { border-color: #2563eb; background: #eff6ff; }
    .store-file-drop .fa { font-size: 1.5rem; color: #2563eb; }
    .store-file-drop small { display: block; color: #64748b; }
    .store-files-preview { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .6rem; }
    .store-files-preview > * { width: 72px; height: 72px; border-radius: 9px; border: 1px solid #e2e8f0; object-fit: cover; }
    .store-files-preview .pdf { display: grid; place-items: center; background: #fef2f2; color: #dc2626; font-size: 1.6rem; }
    .store-ibn-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .55rem .75rem; border-radius: 10px;
                     background: #fef2f2; border: 1px solid #fecaca; font-size: .85rem; }
    .store-ibn-row + .store-ibn-row { margin-top: .4rem; }
    .store-ibn-row a { color: #b91c1c; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
<script>
// Ficheiros da loja (fotografias e IBN): pré-visualização; as fotografias são reduzidas antes de enviar
document.addEventListener('DOMContentLoaded', function () {
    var inputs = Array.from(document.querySelectorAll('.store-upload'));
    if (!inputs.length) return;
    var status = document.getElementById('store-files-status');
    var form = inputs[0].closest('form');

    inputs.forEach(function (input) {
        var preview = document.getElementById(input.id + '-preview');
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.from(input.files).forEach(function (file) {
                var el;
                if (/^image\//.test(file.type)) {
                    el = document.createElement('img');
                    el.src = URL.createObjectURL(file);
                } else {
                    el = document.createElement('div');
                    el.className = 'pdf';
                    el.innerHTML = '<i class="fa fa-file-pdf"></i>';
                }
                el.title = file.name;
                preview.appendChild(el);
            });
        });
    });

    var shrink = function (file) {
        return new Promise(function (resolve) {
            if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size <= 900 * 1024) return resolve(file);
            var img = new Image();
            var url = URL.createObjectURL(file);
            img.onload = function () {
                URL.revokeObjectURL(url);
                var scale = Math.min(1, 1920 / Math.max(img.width, img.height));
                var canvas = document.createElement('canvas');
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function (blob) {
                    resolve(blob && blob.size < file.size ? new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }) : file);
                }, 'image/jpeg', 0.82);
            };
            img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
            img.src = url;
        });
    };

    var photos = document.getElementById('store-photos-input');
    if (form && photos) form.addEventListener('submit', async function (e) {
        if (form.dataset.filesReady === '1' || !photos.files.length || typeof DataTransfer === 'undefined') return;
        e.preventDefault();
        status.textContent = {{ \Illuminate\Support\Js::from(__('A preparar ficheiros…')) }};
        try {
            var dt = new DataTransfer();
            for (var file of Array.from(photos.files)) dt.items.add(await shrink(file));
            photos.files = dt.files;
        } catch (err) { /* envia os originais */ }
        form.dataset.filesReady = '1';
        form.submit();
    });
});
</script>
