{{-- Campo de fotografias: pré-visualização e redução no browser antes de enviar (fotos do telemóvel ficam ~300-600 KB) --}}
@php($inputId = $inputId ?? 'wc-photos')
<div class="wc-photo-input">
    <label class="wc-photo-drop" for="{{ $inputId }}">
        <i class="fa fa-camera"></i>
        <span>
            <strong>{{ __('Tirar ou escolher fotografias') }}</strong>
            <small>{{ __('Pode escolher várias. No telemóvel pode tirar a fotografia na hora.') }}</small>
        </span>
    </label>
    <input type="file" name="fotos[]" id="{{ $inputId }}" class="d-none" accept="image/*" multiple>
    <div class="wc-photo-preview" id="{{ $inputId }}-preview"></div>
    <small class="text-muted d-block" id="{{ $inputId }}-status"></small>
</div>

@once
@push('scripts')
<script>
    window.wcPhotoInput = function (inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var preview = document.getElementById(inputId + '-preview');
        var status = document.getElementById(inputId + '-status');
        var form = input.closest('form');

        input.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.from(input.files).forEach(function (file) {
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;
                preview.appendChild(img);
            });
            status.textContent = input.files.length ? {{ \Illuminate\Support\Js::from(__('Fotografias escolhidas:')) }} + ' ' + input.files.length : '';
        });

        var toImage = function (file) {
            return new Promise(function (resolve, reject) {
                var img = new Image();
                var url = URL.createObjectURL(file);
                img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
                img.onerror = function () { URL.revokeObjectURL(url); reject(); };
                img.src = url;
            });
        };
        var shrink = async function (file) {
            if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size <= 900 * 1024) return file;
            var img = await toImage(file);
            var scale = Math.min(1, 1920 / Math.max(img.width, img.height));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
            var blob = await new Promise(function (r) { canvas.toBlob(r, 'image/jpeg', 0.82); });
            if (!blob || blob.size >= file.size) return file;
            return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
        };

        if (form) {
            form.addEventListener('submit', async function (e) {
                if (form.dataset.photosReady === '1' || !input.files.length || typeof DataTransfer === 'undefined') return;
                e.preventDefault();
                var buttons = form.querySelectorAll('button[type=submit]');
                buttons.forEach(function (b) { b.disabled = true; });
                status.textContent = {{ \Illuminate\Support\Js::from(__('A preparar fotografias…')) }};
                try {
                    var dt = new DataTransfer();
                    for (var file of Array.from(input.files)) dt.items.add(await shrink(file));
                    input.files = dt.files;
                } catch (err) { /* envia os originais */ }
                form.dataset.photosReady = '1';
                status.textContent = {{ \Illuminate\Support\Js::from(__('A enviar…')) }};
                form.submit();
            });
        }
    };
</script>
@endpush
@push('styles')
<style>
    .wc-photo-drop { display: flex; align-items: center; gap: .85rem; padding: .9rem 1rem; border: 2px dashed #cbd5e1; border-radius: 14px; cursor: pointer;
                     background: #f8fafc; color: #334155; margin: 0; transition: border-color .15s, background .15s; }
    .wc-photo-drop:hover { border-color: #2563eb; background: #eff6ff; }
    .wc-photo-drop .fa { font-size: 1.5rem; color: #2563eb; }
    .wc-photo-drop small { display: block; color: #64748b; }
    .wc-photo-preview { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .6rem; }
    .wc-photo-preview img { width: 76px; height: 76px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; }
</style>
@endpush
@endonce

@push('scripts')
<script>document.addEventListener('DOMContentLoaded', function () { window.wcPhotoInput({{ \Illuminate\Support\Js::from($inputId) }}); });</script>
@endpush
