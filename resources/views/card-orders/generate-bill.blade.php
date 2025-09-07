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

<h1 style="text-align: right;">FACTURE DE VENTE</h1>
<p style="text-align: right;">{{ $obj->code }}</p>

<table style="margin-bottom: 25px;">
  <tbody>
    <tr>
      <th>Nom du client</th>
      <td>{{ $full_name }}</td>
    </tr>
    <tr>
      <th>Numéro de telephone du client</th>
      <td>{{ $phone_number }}</td>
    </tr>
    <tr>
      <th>Date</th>
      <td>{{ date('d-m-Y') }}</td>
    </tr>
    <tr>
      <th>Date d'échéance de paiement</th>
      <td>{{ date('d-m-Y') }}</td>
    </tr>
    <tr>
      <th>Numéro d'identification fiscale</th>
      <td>{{ $tin }}</td>
    </tr>
    <tr>
      <th>Centre de coûts</th>
      <td>Vente cartes - SOUNVI ZEHOUE</td>
    </tr>
  </tbody>
</table>

<table id="table">
  <thead>
    <tr>
      <th>Sr</th>
      <th>Description</th>
      <th>Quantité</th>
      <th>Prix</th>
      <th>Montant</th>
    </tr>
  </thead>

  <tbody>
    @php
      $i = 1;
    @endphp

    @foreach ($categories as $category)
      <tr>
        <td>{{ $i++ }}</td>
        <td>
          <div><b>{{ $category->name }}</b></div>
          <div><b>Eligible aux commissions:</b> 1</div>
          <div><b>No de Série:</b></div>
        </td>
        <td>{{ $category->cards->count() }} Unite(s)</td>
        <td>{{ number_format($category->unit_price, 0, ',', ' ') . ' FCFA' }}</td>
        <td>
          {{ number_format($category->unit_price * $category->cards->count(), 0, ',', ' ') . ' FCFA' }}
        </td>
      </tr>

      @foreach ($category->cards as $card)
        <tr>
          <td></td>
          <td>
            <div>{{ $card->card_id }}</div>
          </td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      @endforeach
    @endforeach
  </tbody>
</table>
