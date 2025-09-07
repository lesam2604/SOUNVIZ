let datatable = null;

function initDataTable() {
  datatable = $('#table').DataTable({
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
          return `<a href="/inv-orders/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'client_first_name',
        name: 'client_first_name',
      },
      {
        data: 'client_last_name',
        name: 'client_last_name',
      },
      {
        data: 'created_at',
        name: 'created_at',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/inv-orders/list`,
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
