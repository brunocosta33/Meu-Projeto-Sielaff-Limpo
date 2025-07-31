</main>

<div class="mt-auto"></div>

    <footer class="main-footer">
      <div class="container-xxl">
        <div class="row justify-content-center align-items-center">
          <div class="col-auto col-lg-2 mb-lg-0">
            <img src="{{asset('assets/img/logo-vertical.png')}}" alt="RS Alimentar icon" width="186" height="186" class="img-fluid" />
          </div>
          <hr class="opacity-50 my-4 d-lg-none" />
          <div class="col-lg col-xl-3">
            <div class="d-flex flex-column flex-lg-row align-items-center align-items-lg-start mb-2 text-center text-lg-start">
              <i class="fa fa-envelope my-2 my-lg-1 col-1 text-primary"></i>
              <div class="col"><a href="mailto:info@rsalimentar.com">info@rsalimentar.com</a></div>
            </div>
            <div class="d-flex flex-column flex-lg-row align-items-center align-items-lg-start mb-2 text-center text-lg-start">
              <i class="fa fa-phone my-2 my-lg-1 col-1 text-primary"></i>
              <div class="col">WhatsApp: +351 000 000 000<br>Telemóvel: +351 911890205</div>
            </div>
            <div class="d-flex flex-column flex-lg-row align-items-center align-items-lg-start mb-2 text-center text-lg-start">
              <i class="fa fa-location-dot my-2 my-lg-1 col-1 text-primary"></i>
              <div class="col"><strong>Sede | Head Office:</strong>
                Urbanização Vale Mondego, Lote 1, R/C Esq.
                3140-363 Santo Varão - Coimbra
                <strong>Armazém Maia:</strong>
                Centro Empresarial Maia Park
                Rua Central da Portela, Arm. 15 - 4425-504 Maia
                <br />
                <strong>Armazém Coimbra:</strong>
                Zona Industrial da Pedrulha
                R. Zona Ind. Pedrulha, Arm. F - 3025-662 Coimbra</div>
            </div>
          </div>
          <hr class="opacity-50 my-4 d-lg-none" />
          <div class="col-lg-3 col-xl-auto d-flex flex-column align-items-center align-items-lg-start mb-4 mb-md-0 footer-menu">
            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#modalTermosCondicoes">{{__('Termos & Condições')}}</button>
           
            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#modalPrivacidade">{{__('RGPD | Política de Privacidade')}}</button>
          </div>
        </div>
      </div>
      <div class="bg-light">
        <div class="container-xxl py-3 small text-secondary text-center">
          <div>© <span class="current-year"></span> RS Alimentar - {{ app()->getLocale() === 'pt' ? 'Todos os direitos reservados' : '' }}{{ app()->getLocale() === 'en' ? 'All rights reserved' : '' }} | developed by: <a href="https://www.develop2you.com/" title="DEVELOP2YOU" target="_blank" class="text-decoration-none" style="--bs-link-color: currentColor">DEVELOP2YOU</a></div>
        </div>
      </div>
    </footer>

    <div class="modal fade" id="modalTermosCondicoes" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-title h2">{{ app()->getLocale() === 'pt' ? 'Termos e condições' : '' }}{{ app()->getLocale() === 'en' ? 'Terms and Conditions' : '' }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
             @if(app()->getLocale() === 'en') {!!App\Models\Configuration::first()['text_termos_en']!!} @else {!!App\Models\Configuration::first()['text_termos_pt']!!} @endif
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="modalPrivacidade" tabindex="-1" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-title h2">{{__('Política de Privacidade')}}</div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @if(app()->getLocale() === 'en') {!!App\Models\Configuration::first()['text_rgpd_en']!!} @else {!!App\Models\Configuration::first()['text_rgpd_pt']!!} @endif    
        </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/jquery/1.9.1/jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="{{asset('assets/js/geral.js')}}" type="text/javascript"></script>
</body>
