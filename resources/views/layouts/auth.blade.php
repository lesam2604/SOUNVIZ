<!--
=========================================================
Login Form Bootstrap 1
=========================================================

Product Page: https://uifresh.net
Copyright 2021 UIFresh (https://uifresh.net)
Coded by UIFresh

=========================================================
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. -->
<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/jpeg" href="/assets/images/logo/new-logo.jpg">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="/auth-assets/css/bootstrap.min.css">
  <!-- FontAwesome CSS -->
  <link rel="stylesheet" href="/auth-assets/css/all.min.css">
  <link rel="stylesheet" href="/auth-assets/css/uf-style.css">
  <title>@yield('pageTitle') - SOUNVI ZEHOUE</title>
  <meta name="api_baseurl" content="{{ config('app.api_baseurl') }}">
  <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui@4/material-ui.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/extensions/toastify-js/src/toastify.css">

  @yield('cssPlugins')

  @vite('resources/css/helpers.css')

  <style>
    body {
      height: unset;
      display: block;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-size: cover;
      background-image: linear-gradient(#000000aa, #000000aa), url(/auth-assets/img/world.jpg);
    }

    .uf-form-signin {
      margin-top: 105px;
      max-width: 400px;
    }

    .uf-btn-primary {
      background: #2196F3;
    }

    .uf-btn-primary:hover {
      background: #1769ab;
    }

    a {
      color: #2196F3;
    }

    a:hover {
      color: #1769ab;
    }

    .uf-form-check-input:checked {
      background-color: #2196F3;
      border-color: #2196F3;
    }

    .uf-input-group .form-control {
      box-shadow: unset;
      background: #ffffff;
    }

    #containerScrollingMessages {
      position: fixed;
      top: 0;
      width: 100%;
      z-index: -1;
    }

    .form-label {
      color: white;
    }
  </style>

  @yield('pageCss')
</head>

<body>
  <div id="containerScrollingMessages" style="overflow-x: hidden;"></div>

  <div class="uf-form-signin">
    <div class="text-center">
      <a href="/"
        style="
                background-image: url(/assets/images/logo/new-logo.jpg);
                display: inline-block;
                width: 100px;
                height: 100px;
                background-size: cover;
                border-radius: 50%;
              ">
      </a>
      <h1 class="text-center" style="color: #2196F3;">SOUNVI ZEHOUE</h1>
      <h1 class="text-white h3">@yield('pageTitle')</h1>
    </div>
    @yield('pageContent')
  </div>

  <!-- JavaScript -->

  <!-- Separate Popper and Bootstrap JS -->
  <script src="/auth-assets/js/popper.min.js"></script>
  <script src="/auth-assets/js/bootstrap.min.js"></script>

  <script src="/assets/extensions/jquery/jquery.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/assets/extensions/toastify-js/src/toastify.js"></script>
  <script src="/assets/extensions/validatorjs/dist/validator.js"></script>

  @yield('jsPlugins')

  @vite(['resources/js/helpers.js', 'resources/js/layouts/auth.js'])

  @yield('pageJs')
</body>

</html>
