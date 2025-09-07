let dataTable = null;

async function refreshTotal() {
  let { data } = await ajax({
    url: `${API_BASEURL}/decoders/total-stock`,
    type: 'POST',
    data: {
      company_id: $('#companyId').val(),
    },
  });

  $('#allDecoders').text(data.total);
  $('#activatedDecoders').text(data.activated ?? 0);
  $('#notActivatedDecoders').text(data.not_activated ?? 0);
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
        data: 'decoder_number',
        name: 'decoder_number',
        render: (data, type, row) => {
          return USER.hasRole('reviewer')
            ? `<a href="/decoders/${row.id}">${data}</a>`
            : data;
        },
      },
      {
        data: 'order',
        name: 'order',
        render: (data, type, row) => {
          return `<a href="/decoder-orders/${row.decoder_order_id}">${data}</a>`;
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
            ? `<a href="/operations/canal_activation/${row.operation_id}">${data}</a>`
            : data;
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/decoders/list-stock`,
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
