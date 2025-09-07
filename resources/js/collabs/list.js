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
          return `<a href="/collabs/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'first_name',
        name: 'first_name',
      },
      {
        data: 'last_name',
        name: 'last_name',
      },
      {
        data: 'phone_number',
        name: 'phone_number',
      },
      {
        data: 'email',
        name: 'email',
      },
      {
        data: 'status',
        name: 'status',
        render: (data, type, row) => {
          return data === 'enabled'
            ? `<span class="badge rounded-pill bg-success">Actif</span>`
            : `<span class="badge rounded-pill bg-secondary">Inactif</span>`;
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/collabs/list`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {
        d.status = $('#status').val();
      },
      error: (error) => {
        Swal.fire(error.responseJSON.message, '', 'error');
      },
    },
    pageLength: 25,
    autoWidth: false,
    deferLoading: 0,
  });

  $('#table').wrap('<div style="overflow-x: auto;"></div>');
}

window.render = function () {
  initDataTable();

  $('#status')
    .change(function () {
      datatable.draw();
    })
    .val($('#opStatus').val())
    .change();
};
