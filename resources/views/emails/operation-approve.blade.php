<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Opération {{ $opType->name }} {{ $obj->code }} validée</title>
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
    <h1>Opération {{ $opType->name }} {{ $obj->code }} validée</h1>

    <p>L'opération {{ $opType->name }} {{ $obj->code }} <b class="enabled">a été validée</b>.</p>

    <p>SOUNVI ZEHOUE, Cotonou, Bénin</p>

    <p>Merci de votre confiance.</p>
  </div>
</body>

</html>
