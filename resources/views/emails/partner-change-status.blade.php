<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Le status de votre compte a changé</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #ffffff;
    }

    h1 {
      color: #333;
    }

    p {
      font-size: 16px;
      line-height: 1.6;
      color: #666;
    }

    a {
      color: #007bff;
      text-decoration: none;
    }

    @media (max-width: 600px) {
      .container {
        width: 100%;
        padding: 10px;
      }
    }

    .enabled {
      color: green;
    }

    .disabled {
      color: red;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Le status de votre compte a changé</h1>

    <p>Vous avez reçu cet email car votre compte a été
      @if ($status === 'enabled')
        <b class="enabled">activé.</b>
      @else
        <b class="disabled">désactivé.</b>
      @endif
    </p>

    <p>Si vous n'avez pas demandé cette opération, vous devez contacter l'administrateur du site.</p>

    <p>SOUNVI ZEHOUE, Cotonou, Bénin</p>

    <p>Merci de votre confiance.</p>
  </div>
</body>

</html>
