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
        data: 'code',
        name: 'code',
        render: (data, type, row) => {
          return `<a href="/partners/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'role',
        name: 'role',
        render: (data, type, row) => {
          return {
            'partner-master': `<span class="badge bg-primary">Principal</span>`,
            'partner-pos': `<span class="badge bg-secondary">Boutique</span>`,
          }[data];
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
        data: 'company_name',
        name: 'company_name',
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
        data: 'balance',
        name: 'balance',
        render: (data, type, row) => {
          return data === null ? '' : formatAmount(data);
        },
      },
      {
        data: 'status',
        name: 'status',
        render: (data, type, row) => {
          return {
            pending: `<span class="badge rounded-pill bg-secondary">En attente</span>`,
            enabled: `<span class="badge rounded-pill bg-success">Actif</span>`,
            disabled: `<span class="badge rounded-pill bg-secondary">Inactif</span>`,
            rejected: `<span class="badge rounded-pill bg-danger">Rejeté</span>`,
          }[data];
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/partners/list`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {
        d.status = $('#status').val();
        d.role = $('#role').val();
        d.company_id = $('#companyId').val();
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

async function refreshTotal() {
  let { data: total } = await ajax({
    url: `${API_BASEURL}/partners/total-balances`,
    type: 'GET',
    data: {
      status: $('#status').val(),
      role: $('#role').val(),
      company_id: $('#companyId').val(),
    },
  });

  $('#totalBalance').text(parseInt(total).toLocaleString('fr-FR'));
}

window.render = async function () {
  initDataTable();

  if (USER.hasRole('reviewer')) {
    await populateCompanies('#companyId');
  } else {
    $('#role, #companyId').parent().toggleClass('d-flex d-none');
  }

  $('#status, #role, #companyId').change(function () {
    refreshTotal();
    dataTable.draw();
  });

  $('#status').val($('#opStatus').val()).change();
};
