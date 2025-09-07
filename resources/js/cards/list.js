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
        data: 'card_id',
        name: 'card_id',
        render: (data, type, row) => {
          return `<a href="/cards/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'category',
        name: 'category',
      },
      {
        data: 'sold',
        name: 'sold',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/cards/list`,
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
    $('#cardIdFrom, #cardIdTo').val('');
    $('#modalDeleteRange').modal('show');
  });

  $('#cardIdFrom, #cardIdTo').on('input', function () {
    let value = $(this).val().replace(/\D/g, '').substring(0, 10);
    $(this).val(value);
  });

  $('#deleteRange').click(async function () {
    const cardIdFrom = $('#cardIdFrom').val();
    const cardIdTo = $('#cardIdTo').val();

    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer les cartes de ${cardIdFrom} à ${cardIdTo} ?`,
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
        url: `${API_BASEURL}/cards/delete-range`,
        type: 'POST',
        data: {
          card_id_from: cardIdFrom,
          card_id_to: cardIdTo,
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
