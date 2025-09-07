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
        data: 'name',
        name: 'name',
        render: (data, type, row) => {
          return `<a href="/card-types/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'created_at',
        name: 'created_at',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/card-types/list`,
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

  setTitle('Types de cartes');
};
