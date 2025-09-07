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
          return `<a href="/inv-supplies/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'product_name',
        name: 'product_name',
      },
      {
        data: 'category_name',
        name: 'category_name',
      },
      {
        data: 'quantity',
        name: 'quantity',
      },
      {
        data: 'created_at',
        name: 'created_at',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/inv-supplies/list`,
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
