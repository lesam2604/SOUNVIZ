<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('pageTitle') - SOUNVI ZEHOUE</title>

  <meta name="api_baseurl" content="{{ config('app.api_baseurl') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="/assets/css/main/app.css">
  <link rel="stylesheet" href="/assets/css/main/app-dark.css">
  <link rel="icon" type="image/jpeg" href="/assets/images/logo/new-logo.jpg">

  <link rel="stylesheet" href="/assets/css/pages/fontawesome.css">
  <link rel="stylesheet" href="/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="/assets/css/pages/datatables.css">

  <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui@4/material-ui.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/extensions/toastify-js/src/toastify.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
  @yield('cssPlugins')

  @vite('resources/css/helpers.css')

  @yield('pageCss')
</head>

<body class="{{ ($_COOKIE['user-type'] ?? 'partner') === 'admin' ? 'theme-dark' : '' }} loading">
  @include('partials.preloader')

  <div id="app">
    <div id="sidebar" class="active">
      <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
          <div class="d-flex justify-content-center align-items-center">
            <div class="logo">
              <a href="/"
                style="
                background-image: url(/assets/images/logo/new-logo.jpg);
                display: block;
                width: 100px;
                height: 100px;
                background-size: cover;
                border-radius: 50%;
              ">
              </a>
            </div>
            <div class="theme-toggle d-flex gap-2  align-items-center mt-2" style="display: none !important;">
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                role="img" class="iconify iconify--system-uicons" width="20" height="20"
                preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                  stroke-linejoin="round">
                  <path
                    d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                    opacity=".3"></path>
                  <g transform="translate(-210 -1)">
                    <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                    <circle cx="220.5" cy="11.5" r="4"></circle>
                    <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                  </g>
                </g>
              </svg>
              <div class="form-check form-switch fs-6">
                <input class="form-check-input  me-0" type="checkbox" id="toggle-dark">
                <label class="form-check-label"></label>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                role="img" class="iconify iconify--mdi" width="20" height="20"
                preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                <path fill="currentColor"
                  d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32 .45-.66 .87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93 .36 1.85 1.19c-.27 2.86 .69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96 .31 10.98c3.02 3.01 7.84 3.12 10.98 .31Z">
                </path>
              </svg>
            </div>
            <div class="sidebar-toggler  x">
              <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
            </div>
          </div>
        </div>

        <div class="sidebar-menu">
          @include('partials.menus.' . ($_COOKIE['user-type'] ?? 'partner'))
        </div>
      </div>
    </div>
    <div id="main" class='layout-navbar'>
      <header class='mb-3'>
        @include('partials.navbar')
      </header>

      <div id="main-content">

        <div class="page-heading">
          <div id="containerScrollingMessages" class="overflow-hidden"></div>
          @yield('pageContent')
        </div>

        @include('partials.footer')
      </div>
    </div>
  </div>

  <div class="modal" tabindex="-1" id="modalChangePassword">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Changer votre mot de passe</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="row">
            <div class="col-12 mb-3">
              <label for="password" class="form-label">Mot de passe actuel</label>
              <input type="password" value="" id="password" class="form-control">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12 mb-3">
              <label for="newPassword" class="form-label">Nouveau mot de passe</label>
              <input type="password" value="" id="newPassword" class="form-control">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12 mb-3">
              <label for="confirmNewPassword" class="form-label">Confirmez votre nouveau mot de passe</label>
              <input type="password" value="" id="confirmNewPassword" class="form-control">
              <div class="invalid-feedback"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="btnChangePassword"><i class="fas fa-save"></i>
            Sauvegarder</button>
        </div>
      </div>
    </div>
  </div>

  <script src="/assets/js/bootstrap.js"></script>

  <script src="/assets/extensions/jquery/jquery.min.js"></script>


<script>
  // CSRF global pour toutes les requêtes jQuery AJAX
  (function () {
    var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    window.LARAVEL_CSRF_TOKEN = token;
    if (window.jQuery) {
      jQuery.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      console.log('[csrf] ajaxSetup chargé, token présent:', !!token);
    } else {
      console.warn('[csrf] jQuery non trouvé avant ajaxSetup');
    }
  })();
</script>






  <script src="https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/assets/extensions/toastify-js/src/toastify.js"></script>
  <script src="/assets/extensions/validatorjs/dist/validator.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- === CSRF setup (jQuery + fallback champs formulaires) ================= -->
  <script>
    (function () {
      const meta = document.querySelector('meta[name="csrf-token"]');
      const csrf = meta ? meta.getAttribute('content') : '';
      if (!csrf) return;

      // Dispo globale si besoin ailleurs
      window.LARAVEL_CSRF_TOKEN = csrf;

      // jQuery: ajoute le header automatiquement à toutes les requêtes
      if (window.$ && $.ajaxSetup) {
        $.ajaxSetup({
          headers: { 'X-CSRF-TOKEN': csrf }
        });
      }

      // Fallback: si un <form> est soumis sans _token, on l'ajoute
      document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && form.tagName === 'FORM' && !form.querySelector('input[name="_token"]')) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = '_token';
          input.value = csrf;
          form.appendChild(input);
        }
      }, true);
    })();
  </script>
  <!-- ====================================================================== -->

  @yield('jsPlugins')

  @vite(['resources/js/helpers.js', 'resources/js/layouts/app.js'])

  @yield('pageJs')
</body>

</html>
