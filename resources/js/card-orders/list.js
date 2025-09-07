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
        data: 'code',
        name: 'code',
        render: (data, type, row) => {
          return `<a href="/card-orders/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'nbcards',
        name: 'nbcards',
      },
      {
        data: 'client',
        name: 'client',
      },
      {
        data: 'client_code',
        name: 'client_code',
        render: (data, type, row) => {
          if (row.extra_client_id === null) {
            return `<a href="/partners/${row.partner_id}">${data}</a>`;
          } else {
            return `<a href="/extra-clients/${row.extra_client_id}">${data}</a>`;
          }
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/card-orders/list`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {},
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
};
