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

<h1>Opérations «{{ $opType->name }}»</h1>

<table style="margin-bottom: 25px;">
  <tbody>
    <tr>
      <th>Partenaire</th>
      <td>{{ $partner ? $partner->user->full_name : 'Tout' }}</td>
    </tr>
    <tr>
      <th>Status</th>
      <td>{{ $status }}</td>
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
      <th>No</th>
      <th>Code</th>
      <th>Status</th>

      @foreach ($opType->sorted_fields as [$fieldName, $fieldData])
        @if ($fieldData->listed)
          <th>{{ $fieldData->label }}</th>
        @endif
      @endforeach
    </tr>
  </thead>

  <tbody>
    @php
      $i = 1;
    @endphp

    @foreach ($rows as $row)
      <tr>
        <td>{{ $i++ }}</td>
        <td>{{ $row->code }}</td>
        <td>{{ $row->status }}</td>

        @php
          $rowData = json_decode($row->data);
        @endphp

        @foreach ($opType->sorted_fields as [$fieldName, $fieldData])
          @if ($fieldData->listed)
            <td>
              {{ $fieldData->is_amount ? number_format($rowData->$fieldName, 0, ',', ' ') . ' FCFA' : $rowData->$fieldName }}
            </td>
          @endif
        @endforeach
      </tr>
    @endforeach
  </tbody>
</table>
