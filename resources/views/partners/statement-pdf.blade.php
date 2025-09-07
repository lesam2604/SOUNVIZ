<style>
  table {
    border-collapse: collapse;
    width: 100%;
  }

  td,
  th {
    border: 1px solid #dddddd;
    padding: 8px;
  }

  #table>tbody tr:nth-child(even) {
    background-color: #f2f2f2;
  }

  thead {
    background-color: #333;
    color: white;
  }

  th {
    text-align: left;
  }
</style>

<h1>Relevé des opérations</h1>

<table style="margin-bottom: 25px;">
  <tbody>
    <tr>
      <th>Partenaire</th>
      <td>{{ $partner ? $partner->user->full_name : 'Tout' }}</td>
    </tr>
    <tr>
      <th>De</th>
      <td>{{ $fromDate ?? '' }}</td>
    </tr>
    <tr>
      <th>A</th>
      <td>{{ $toDate ?? '' }}</td>
    </tr>
  </tbody>
</table>

<table id="table">
  <thead>
    <tr>
      <th>Date</th>
      <th>Partenaire</th>
      <th>Opération</th>
      <th>Montant</th>
      <th>Solde</th>
    </tr>
  </thead>

  <tbody>
    @foreach ($rows as $row)
      <tr>
        <td>{{ $row->created_at }}</td>
        <td>{{ $row->partner }}</td>
        <td>{{ $row->type }}</td>
        <td>{{ number_format($row->amount, 0, ',', '.') }}</td>
        <td>{{ number_format($row->balance, 0, ',', '.') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
