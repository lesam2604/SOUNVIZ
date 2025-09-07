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
          return `<a href="/balance-adjustments/${row.id}">${data}</a>`;
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
        data: 'old_balance',
        name: 'old_balance',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'amount_to_withdraw',
        name: 'amount_to_withdraw',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'balance',
        name: 'balance',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'reason',
        name: 'reason',
        render: (data, type, row) => {
          return truncate(data);
        },
      },
      {
        data: 'created_at',
        name: 'created_at',
      },
    ],
    order: [8, 'desc'],
    ajax: {
      url: `${API_BASEURL}/balance-adjustments/list`,
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
