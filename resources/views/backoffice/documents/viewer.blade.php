<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title>{{ $document->titulo }} - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; background: #525659; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .bar { position: fixed; top: 0; left: 0; right: 0; height: 56px; z-index: 10; display: flex; align-items: center; gap: .6rem;
               padding: 0 .75rem; background: #1f2937; color: #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .3); }
        .bar a { color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .7rem;
                 border-radius: 8px; font-size: .95rem; white-space: nowrap; }
        .bar .back { background: #1976d2; font-weight: 600; }
        .bar .download { background: rgba(255, 255, 255, .12); }
        .bar .title { flex: 1; min-width: 0; font-weight: 600; font-size: .95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .content { position: absolute; top: 56px; left: 0; right: 0; bottom: 0; }
        iframe { width: 100%; height: 100%; border: 0; display: block; }
        #pages { overflow-y: auto; -webkit-overflow-scrolling: touch; height: 100%; padding: 10px 0 30px; }
        #pages canvas { display: block; margin: 0 auto 10px; background: #fff; box-shadow: 0 1px 4px rgba(0, 0, 0, .4); max-width: 100%; }
        .status { color: #eee; text-align: center; padding: 2rem 1rem; }
        .status a { color: #90caf9; }
        @media (max-width: 480px) { .bar .label { display: none; } }
    </style>
</head>
<body>
    <div class="bar">
        <a href="{{ $backUrl }}" class="back"><i class="fa fa-arrow-left"></i> <span class="label">{{ __('Voltar') }}</span></a>
        <div class="title" title="{{ $document->titulo }}">{{ $document->titulo }}</div>
        <a href="{{ route('backoffice.documents.download', $document->id) }}" class="download" title="{{ __('Descarregar') }}"><i class="fa fa-download"></i> <span class="label">{{ __('Descarregar') }}</span></a>
    </div>

    <div class="content" id="content">
        <div class="status" id="status">{{ __('A carregar o documento…') }}</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function () {
            var pdfUrl = @json(route('backoffice.documents.view', $document->id));
            var content = document.getElementById('content');
            var status = document.getElementById('status');
            var touch = window.matchMedia('(pointer: coarse)').matches || window.innerWidth < 900;

            // Computador: leitor de PDF do próprio browser (pesquisa, impressão)
            if (!touch) {
                content.innerHTML = '<iframe src="' + pdfUrl + '" title="PDF"></iframe>';
                return;
            }

            // Telemóvel/tablet: desenhar as páginas dentro do site com PDF.js
            if (!window.pdfjsLib) {
                status.innerHTML = 'Não foi possível abrir o visualizador. <a href="' + pdfUrl + '">Abrir o PDF</a>';
                return;
            }
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            var pages = document.createElement('div');
            pages.id = 'pages';

            pdfjsLib.getDocument({ url: pdfUrl, withCredentials: true }).promise.then(function (pdf) {
                content.innerHTML = '';
                content.appendChild(pages);
                var width = Math.min(pages.clientWidth - 16, 1100);
                var ratio = window.devicePixelRatio || 1;

                // Uma página de cada vez, para não pesar no telemóvel
                var render = function (n) {
                    if (n > pdf.numPages) return;
                    pdf.getPage(n).then(function (page) {
                        var base = page.getViewport({ scale: 1 });
                        var scale = width / base.width;
                        var viewport = page.getViewport({ scale: scale * ratio });
                        var canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        canvas.style.width = (viewport.width / ratio) + 'px';
                        pages.appendChild(canvas);
                        page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise.then(function () { render(n + 1); });
                    });
                };
                render(1);
            }).catch(function () {
                status.innerHTML = 'Não foi possível abrir o documento. <a href="' + pdfUrl + '">Tentar abrir o PDF diretamente</a>';
            });
        })();
    </script>
</body>
</html>
