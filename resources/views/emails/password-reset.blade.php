<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Réinitialisation du mot de passe</title>
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
  </style>
</head>

<body>
  <div class="container">
    <h1>Réinitialisation du mot de passe</h1>

    <p>Vous avez reçu cet email car vous avez demandé la réinitialisation de votre mot de passe sur notre site.</p>

    <a href="{{ config('app.app_baseurl') }}/set-password?email={{ $email }}&token={{ $token }}">Cliquez ici pour
      réinitialiser votre mot de passe.</a>

    <p>SOUNVI ZEHOUE, Cotonou, Bénin</p>

    <p>Merci de votre confiance.</p>
  </div>
</body>

</html>
