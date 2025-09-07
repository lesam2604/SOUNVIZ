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
          return `<a href="/money-transfers/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'sender_name',
        name: 'sender_name',
      },
      {
        data: 'sender_code',
        name: 'sender_code',
      },
      {
        data: 'recipient_name',
        name: 'recipient_name',
      },
      {
        data: 'recipient_code',
        name: 'recipient_code',
      },
      {
        data: 'amount',
        name: 'amount',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/money-transfers/list`,
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
