<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Opération {{ $opType->name }} {{ $obj->code }} mise a jour</title>
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
    <h1>Opération {{ $opType->name }} {{ $obj->code }} mise a jour</h1>

    <p>L'opération {{ $opType->name }} {{ $obj->code }} initiée par le partenaire
      {{ $obj->partner->user->full_name }} {{ $obj->partner->code }} a été mise a jour</p>

    <table>
      <tbody>
        <tr>
          <td>Code</td>
          <td>{{ $obj->code }}</td>
        </tr>
        @foreach ($opType->fields as $fieldName => $fieldData)
          <tr>
            <td>{{ $fieldData->label }}</td>
            <td>
              @if ($opType->amount_field === $fieldName)
                {{ number_format($obj->amount, 0, ',', ' ') }}
              @elseif ($fieldData->type === 'number')
                {{ number_format($obj->data->$fieldName, 0, ',', ' ') }}
              @else
                {{ $obj->data->$fieldName }}
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <p>SOUNVI ZEHOUE, Cotonou, Bénin</p>

    <p>Merci de votre confiance.</p>
  </div>
</body>

</html>
