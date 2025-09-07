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
          return `<a href="/extra-clients/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'company_name',
        name: 'company_name',
      },
      {
        data: 'tin',
        name: 'tin',
      },
      {
        data: 'phone_number',
        name: 'phone_number',
      },
      {
        data: 'first_name',
        name: 'first_name',
      },
      {
        data: 'last_name',
        name: 'last_name',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/extra-clients/list`,
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
};
