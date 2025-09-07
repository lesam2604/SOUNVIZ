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

<h1>Carte «{{ $op->data->card_id }}»</h1>

<table style="margin-bottom: 25px; page-break-after: always;">
  <tbody>
    @foreach ($op->operationType->sorted_fields as [$fieldName, $fieldData])
      <tr>
        <th>{{ $fieldData->label }}</th>
        <td>
          @if ($fieldData->is_amount)
            {{ number_format($op->data->$fieldName, 0, ',', ' ') . ' FCFA' }}
          @elseif ($fieldData->type === 'file')
            <?php
            $path = storage_path('app/public/uploads/' . $op->data->$fieldName);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            ?>
            <img src="{{ $base64 }}" style="width: 500px;">
          @else
            {{ $op->data->$fieldName }}
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
