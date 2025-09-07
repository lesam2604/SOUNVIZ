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
          return `<a href="/tickets/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'issue',
        name: 'issue',
        render: (data, type, row) => {
          return truncate(data);
        },
      },
      {
        data: 'response',
        name: 'response',
        render: (data, type, row) => {
          return data === null ? '' : truncate(data);
        },
      },
      {
        data: 'partner',
        name: 'partner',
      },
      {
        data: 'partner_code',
        name: 'partner_code',
      },
      {
        data: 'created_at',
        name: 'created_at',
      },
      {
        data: 'responded_at',
        name: 'responded_at',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/tickets/list`,
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
    .val($('#objStatus').val())
    .change();
};
