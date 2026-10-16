<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=6">
    <title>{{ $document->titulo }} - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; background: #525659; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .bar { position: fixed; top: 0; left: 0; right: 0; height: 56px; z-index: 10; display: flex; align-items: center; gap: .6rem;
               padding: 0 .75rem; padding-top: env(safe-area-inset-top); height: calc(56px + env(safe-area-inset-top));
               background: #1f2937; color: #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .3); }
        .bar a { color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .8rem;
                 border-radius: 9px; font-size: .95rem; white-space: nowrap; }
        .bar .back { background: #1976d2; font-weight: 600; }
        .bar .download { background: rgba(255, 255, 255, .14); }
        .bar .title { flex: 1; min-width: 0; font-weight: 600; font-size: .92rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        #pages { padding: calc(66px + env(safe-area-inset-top)) 0 30px; }
        .page { margin: 0 auto 10px; background: #fff; box-shadow: 0 1px 4px rgba(0, 0, 0, .4); position: relative; }
        .page canvas { display: block; width: 100%; height: 100%; }
        .page .num { position: absolute; bottom: 4px; right: 8px; font-size: .7rem; color: #9aa0a6; }
        .status { color: #eee; text-align: center; padding: calc(90px + env(safe-area-inset-top)) 1rem 0; }
        .status .bar-progress { width: 220px; height: 6px; margin: 12px auto 0; background: rgba(255, 255, 255, .2); border-radius: 3px; overflow: hidden; }
        .status .bar-progress div { height: 100%; width: 0; background: #64b5f6; transition: width .2s; }
        .status a { color: #90caf9; }
    </style>
</head>
<body>
    <div class="bar">
        <a href="{{ $backUrl }}" class="back" id="back"><i class="fa fa-arrow-left"></i> {{ __('Voltar') }}</a>
        <div class="title">{{ $document->titulo }}</div>
        <a href="{{ route('backoffice.documents.download', $document->id) }}" class="download" title="{{ __('Descarregar') }}"><i class="fa fa-download"></i></a>
    </div>

    <div class="status" id="status">
        {{ __('A carregar o documento…') }} <span id="pct"></span>
        <div class="bar-progress"><div id="progress"></div></div>
    </div>
    <div id="pages"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function () {
            var pdfUrl = {{ \Illuminate\Support\Js::from(route('backoffice.documents.view', $document->id)) }};
            var status = document.getElementById('status');
            var pagesEl = document.getElementById('pages');
            var esc = function (t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; };

            // "Voltar": regressa à página anterior do site; se não houver, vai para a Documentação
            document.getElementById('back').addEventListener('click', function (e) {
                if (document.referrer && document.referrer.indexOf(location.origin) === 0 && history.length > 1) {
                    e.preventDefault();
                    history.back();
                }
            });

            if (!window.pdfjsLib) {
                status.innerHTML = esc(@json(__('Não foi possível abrir o visualizador.'))) + ' <a href="' + pdfUrl + '">' + esc(@json(__('Abrir o PDF'))) + '</a>';
                return;
            }
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            var task = pdfjsLib.getDocument({ url: pdfUrl, withCredentials: true });
            task.onProgress = function (p) {
                if (p.total) {
                    var pct = Math.min(100, Math.round(p.loaded / p.total * 100));
                    document.getElementById('pct').textContent = pct + '%';
                    document.getElementById('progress').style.width = pct + '%';
                }
            };

            task.promise.then(function (pdf) {
                status.style.display = 'none';
                var width = Math.min(window.innerWidth - 16, 1100);
                var ratio = Math.min(window.devicePixelRatio || 1, 2); // nitidez sem pesar demasiado

                // 1.ª página dá a proporção; criam-se "molduras" para todas e só se desenham as que estão à vista
                pdf.getPage(1).then(function (first) {
                    var base = first.getViewport({ scale: 1 });
                    var height = Math.round(width * base.height / base.width);
                    var rendered = {};

                    var render = function (n, el) {
                        if (rendered[n]) return;
                        rendered[n] = true;
                        pdf.getPage(n).then(function (page) {
                            var vp1 = page.getViewport({ scale: 1 });
                            var scale = width / vp1.width;
                            var viewport = page.getViewport({ scale: scale * ratio });
                            el.style.height = Math.round(vp1.height * scale) + 'px';
                            var canvas = document.createElement('canvas');
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            el.insertBefore(canvas, el.firstChild);
                            page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });
                        });
                    };

                    var observer = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) render(+entry.target.dataset.page, entry.target);
                        });
                    }, { rootMargin: '800px 0px' }) : null;

                    for (var n = 1; n <= pdf.numPages; n++) {
                        var el = document.createElement('div');
                        el.className = 'page';
                        el.dataset.page = n;
                        el.style.width = width + 'px';
                        el.style.height = height + 'px';
                        el.innerHTML = '<span class="num">' + n + ' / ' + pdf.numPages + '</span>';
                        pagesEl.appendChild(el);
                        if (observer) observer.observe(el); else render(n, el);
                    }
                });
            }).catch(function () {
                status.innerHTML = esc(@json(__('Não foi possível abrir o documento.'))) + ' <a href="' + pdfUrl + '">' + esc(@json(__('Tentar abrir o PDF diretamente'))) + '</a>';
            });
        })();
    </script>
</body>
</html>
