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
      },
      {
        data: 'name',
        name: 'name',
      },
      {
        data: 'commission',
        name: 'commission',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'reviewed_at',
        name: 'reviewed_at',
      },
      {
        data: 'partner',
        name: 'partner',
      },
      {
        data: 'partner_code',
        name: 'partner_code',
      },
    ],
    order: [4, 'desc'],
    ajax: {
      url: `${API_BASEURL}/commissions/list-partners`,
      type: 'POST',
      dataType: 'json',
      error: (error) => {
        Swal.fire(error.responseJSON.message, '', 'error');
      },
      data: (d) => {
        d.partner_id = $('#partnerId').val();
        d.from_date = $('#fromDate').val();
        d.to_date = $('#toDate').val();
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
    url: `${API_BASEURL}/commissions/total-partners`,
    type: 'GET',
    data: {
      partner_id: $('#partnerId').val(),
      from_date: $('#fromDate').val(),
      to_date: $('#toDate').val(),
    },
  });

  $('#total').text(parseInt(total).toLocaleString('fr-FR'));
}

window.render = function () {
  initDataTable();

  $('#partnerId, #fromDate, #toDate').change(function () {
    refreshTotal();
    datatable.draw();
  });

  $('#partnerId').change();

  if (USER.hasRole('partner')) {
    $('#partnerId').parent().removeClass('d-flex').addClass('d-none');
  } else {
    populatePartners('#partnerId');
  }
};
