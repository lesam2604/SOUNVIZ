window.dataTableDecoderNumbers = null;

window.clearDecoderAddingTypes = function () {
  $('#type').val('one').change().parent().show();
  $('#decoderNumber').val('');
  $('#decoderNumberMany').val('');
  dataTableDecoderNumbers.clear().draw();
  $('#decoderNumberFrom').val('');
  $('#decoderNumberTo').val('');
};

window.fillDecoderAddingTypes = function (data) {
  data.type = $('#type').val();

  if (data.type === 'one') {
    data.decoder_number = $('#decoderNumber').val();
  } else if (data.type === 'many') {
    data.decoder_numbers = dataTableDecoderNumbers
      .rows()
      .data()
      .toArray()
      .map((row) => row.decoder_number);
  } else if (data.type === 'range') {
    data.decoder_number_from = $('#decoderNumberFrom').val();
    data.decoder_number_to = $('#decoderNumberTo').val();
  }

  return data;
};

window.fillDecoderAddingErrorFields = function (errorFields) {
  errorFields.type = '#type';
  errorFields.decoder_number = '#decoderNumber';
  errorFields.decoder_numbers = '#decoderNumberMany';
  errorFields.decoder_number_from = '#decoderNumberFrom';
  errorFields.decoder_number_to = '#decoderNumberTo';

  return errorFields;
};

window.initDataTableDecoderNumbers = function () {
  dataTableDecoderNumbers = $('#tableDecoderNumbers')
    .on('click', '.remove', function (e) {
      dataTableDecoderNumbers.row($(this).closest('tr')).remove().draw(false);
    })
    .DataTable({
      processing: true,
      columns: [
        {
          data: 'decoder_number',
        },
        {
          orderable: false,
          render: (data, type, row) => {
            return `
              <button type="button" class="btn btn-sm btn-danger m-1 remove"><i class="fas fa-times"></i> Retirer</button>
            `;
          },
        },
      ],
      order: [0, 'desc'],
      pageLength: 25,
      autoWidth: false,
    });
};

window.initDecoderAddingTypes = function () {
  initDataTableDecoderNumbers();

  $('#addDecoderNumber').click(function (e) {
    let decoderNumber = $('#decoderNumberMany').val();

    if (decoderNumber.length !== 14) {
      Swal.fire('Le numéro doit être composé de 14 chiffres', '', 'error');
      return;
    }

    let matchingRows = dataTableDecoderNumbers
      .rows()
      .data()
      .filter((row) => {
        return row.decoder_number === decoderNumber;
      });

    if (matchingRows.length > 0) {
      Swal.fire('Ce numéro est déjà dans la liste', '', 'error');
      return;
    }

    dataTableDecoderNumbers.row
      .add({ decoder_number: decoderNumber })
      .draw(false);
  });

  $('#type').change(function (e) {
    let types = ['one', 'many', 'range'];

    for (const type of types) {
      if (type === $(this).val()) {
        $(`#${type}Block`).show();
      } else {
        $(`#${type}Block`).hide();
      }
    }
  });

  $(
    '#decoderNumber, #decoderNumberMany, #decoderNumberFrom, #decoderNumberTo'
  ).on('input', function () {
    let value = $(this).val().replace(/\D/g, '').substring(0, 14);
    $(this).val(value);
  });
};
