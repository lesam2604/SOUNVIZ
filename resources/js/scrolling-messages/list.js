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
        data: 'label',
        name: 'label',
        render: (data, type, row) => {
          return `<a href="/scrolling-messages/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'from',
        name: 'from',
      },
      {
        data: 'to',
        name: 'to',
      },
      {
        data: 'show_auth',
        name: 'show_auth',
        render: (data, type, row) => {
          return data ? 'Oui' : 'Non';
        },
      },
      {
        data: 'show_app',
        name: 'show_app',
        render: (data, type, row) => {
          return data ? 'Oui' : 'Non';
        },
      },
      {
        data: 'status',
        name: 'status',
        render: (data, type, row) => {
          return {
            enabled: `<span class="badge rounded-pill bg-success">Actif</span>`,
            disabled: `<span class="badge rounded-pill bg-secondary">Inactif</span>`,
          }[data];
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/scrolling-messages/list`,
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
