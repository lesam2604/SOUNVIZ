<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Partenaire en attente de validation</title>
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

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f2f2f2;
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
    <h1>Partenaire en attente de validation</h1>

    <p>Un nouveau partenaire est en attente de validation.</p>

    <table>
      <tbody>
        <tr>
          <th>Attribut</th>
          <th>Valeur</th>
        </tr>
        <tr>
          <td>Code</td>
          <td>{{ $partner->user->code }}</td>
        </tr>
        <tr>
          <td>Nom</td>
          <td>{{ $partner->user->last_name }}</td>
        </tr>
        <tr>
          <td>Prénom</td>
          <td>{{ $partner->user->first_name }}</td>
        </tr>
        <tr>
          <td>Nom d’établissement</td>
          <td>{{ $partner->company_name }}</td>
        </tr>
        <tr>
          <td>Numéro de telephone</td>
          <td>{{ $partner->user->phone_number }}</td>
        </tr>
        <tr>
          <td>Email</td>
          <td>{{ $partner->user->email }}</td>
        </tr>
        <tr>
          <td>Numéro de la carte d’identité</td>
          <td>{{ $partner->idcard_number }}</td>
        </tr>
        <tr>
          <td>Numéro IFU</td>
          <td>{{ $partner->tin }}</td>
        </tr>
        <tr>
          <td>Adresse</td>
          <td>{{ $partner->address }}</td>
        </tr>
      </tbody>
    </table>

    <p>SOUNVI ZEHOUE, Cotonou, Bénin</p>

    <p>Merci de votre confiance.</p>
  </div>
</body>

</html>
