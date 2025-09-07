let datatable = null;

async function sentOtp() {
  try {
    let swalResult = await Swal.fire({
      title:
        'Voulez-vous vraiment effectuer un retrait de toutes vos commissions?',
      text: 'Cette opération est irreversible',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });

    if (!swalResult.isConfirmed) {
      throw {};
    }

    swalLoading();

    let { data } = await ajax({
      url: `${API_BASEURL}/withdrawals/send-otp-code`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    $('#otpCode').val('');
    $('#modalOtp').modal('show');
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function addObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/withdrawals/store`,
      type: 'POST',
      data: {
        otp_code: $('#otpCode').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    $('#modalOtp').modal('hide');
    datatable.draw(false);
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

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
          return `<a href="/withdrawals/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'amount',
        name: 'amount',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'created_at',
        name: 'created_at',
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
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/withdrawals/list`,
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

  $('#withdraw').click(function (e) {
    sentOtp();
  });

  $('#confirmOtp').click(function (e) {
    addObject();
  });
};
