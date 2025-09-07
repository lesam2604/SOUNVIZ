let datatable = null;

function initDataTable() {
  datatable = $('#table').DataTable({
    processing: true,
    serverSide: true,
    columns: [
      {
        data: 'created_at',
        name: 'created_at',
      },
      {
        data: 'partner',
        name: 'partner',
      },
      {
        data: 'type',
        name: 'type',
      },
      {
        data: 'amount',
        name: 'amount',
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
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/partners/statement-list`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {
        d.from_date = $('#fromDate').val();
        d.to_date = $('#toDate').val();
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

async function otherInits() {
  setTitle(`Relevé des opérations`);

  $('#exportExcel').click(function (e) {
    e.preventDefault();

    downloadFile(`${API_BASEURL}/partners/export-excel-statement`, 'GET', {
      from_date: $('#fromDate').val(),
      to_date: $('#toDate').val(),
    });
  });

  $('#exportPdf').click(async function (e) {
    e.preventDefault();

    swalLoading();

    await downloadFile(`${API_BASEURL}/partners/export-pdf-statement`, 'GET', {
      from_date: $('#fromDate').val(),
      to_date: $('#toDate').val(),
    });

    Swal.close();
  });

  $('#fromDate, #toDate').change(function () {
    datatable.draw();
  });

  $('#fromDate').change();
}

window.render = async function () {
  initDataTable();
  await otherInits();
};
