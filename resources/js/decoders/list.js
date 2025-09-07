let dataTable = null;

function initDataTable() {
  dataTable = $('#table').DataTable({
    processing: true,
    serverSide: true,
    columns: [
      {
        data: '__no__',
        name: 'id',
      },
      {
        data: 'decoder_number',
        name: 'decoder_number',
        render: (data, type, row) => {
          return `<a href="/decoders/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'sold',
        name: 'sold',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/decoders/list`,
      type: 'POST',
      dataType: 'json',
      error: (error) => {
        Swal.fire(error.responseJSON.message, '', 'error');
      },
    },
    pageLength: 25,
    autoWidth: false,
  });

  $('#table').wrap('<div style="overflow-x: auto;"></div>');
}

window.render = function () {
  initDataTable();

  $('#showModalDeleteRange').click(function () {
    $('#decoderNumberFrom, #decoderNumberTo').val('');
    $('#modalDeleteRange').modal('show');
  });

  $('#decoderNumberFrom, #decoderNumberTo').on('input', function () {
    let value = $(this).val().replace(/\D/g, '').substring(0, 14);
    $(this).val(value);
  });

  $('#deleteRange').click(async function () {
    const decoderNumberFrom = $('#decoderNumberFrom').val();
    const decoderNumberTo = $('#decoderNumberTo').val();

    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer les décodeurs de ${decoderNumberFrom} à ${decoderNumberTo} ?`,
      text: 'Cette opération est irreversible',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });

    if (!swalResult.isConfirmed) return;

    try {
      swalLoading();

      let { data } = await ajax({
        url: `${API_BASEURL}/decoders/delete-range`,
        type: 'POST',
        data: {
          decoder_number_from: decoderNumberFrom,
          decoder_number_to: decoderNumberTo,
        },
      });

      Toast.fire(data.message, '', 'success');
      dataTable.draw();
    } catch ({ error }) {
      console.log(error);
      Swal.fire(error.responseJSON.message, '', 'error');
    }
  });
};
