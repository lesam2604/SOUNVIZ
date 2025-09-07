let dataTable = null;

async function refreshTotal() {
  let { data } = await ajax({
    url: `${API_BASEURL}/cards/total-stock`,
    type: 'POST',
    data: {
      company_id: $('#companyId').val(),
    },
  });

  $('#allCards').text(data.total);
  $('#activatedCards').text(data.activated ?? 0);
  $('#notActivatedCards').text(data.not_activated ?? 0);
}

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
          return USER.hasRole('reviewer')
            ? `<a href="/cards/${row.id}">${data}</a>`
            : data;
        },
      },
      {
        data: 'category',
        name: 'category',
        render: (data, type, row) => {
          return USER.hasRole('reviewer')
            ? `<a href="/card-categories/${row.card_category_id}">${data}</a>`
            : data;
        },
      },
      {
        data: 'order',
        name: 'order',
        render: (data, type, row) => {
          return `<a href="/card-orders/${row.card_order_id}">${data}</a>`;
        },
      },
      {
        data: 'company',
        name: 'company',
      },
      {
        data: 'partner',
        name: 'partner',
        render: (data, type, row) => {
          return USER.hasRole('reviewer')
            ? `<a href="/partners/${row.partner_id}">${data}</a>`
            : data;
        },
      },
      {
        data: 'activated',
        name: 'activated',
        render: (data, type, row) => {
          return {
            Oui: `<span class="badge bg-success">Oui</span>`,
            Non: `<span class="badge bg-danger">Non</span>`,
          }[data];
        },
      },
      {
        data: 'operation_code',
        name: 'operation_code',
        render: (data, type, row) => {
          return data === null
            ? ''
            : USER.hasRole('reviewer')
            ? `<a href="/operations/card_activation/${row.operation_id}">${data}</a>`
            : data;
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/cards/list-stock`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {
        d.company_id = $('#companyId').val();
        d.status = $('#status').val();
      },
      error: (error) => {
        console.log(error);
        Swal.fire(error.responseJSON.message, '', 'error');
      },
    },
    pageLength: 25,
    autoWidth: false,
    deferLoading: 0,
  });

  $('#table').wrap('<div style="overflow-x: auto;"></div>');
}

window.render = async function () {
  initDataTable();

  $('#companyId').change(function () {
    refreshTotal();
  });

  $('#companyId, #status').change(function () {
    dataTable.draw();
  });

  $('#status').val($('#paramStatus').val());

  $('#companyId').change();

  if (USER.hasRole('reviewer')) {
    populateCompanies('#companyId');
  } else {
    $('#companyId').parent().toggleClass('d-flex d-none');
  }

  $('[data-status]').click(function () {
    $('#status').val($(this).data('status')).change();
  });
};
