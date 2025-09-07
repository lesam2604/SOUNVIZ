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
          return `<a href="/broadcast-messages/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'group',
        name: 'group',
        searchable: false,
        render: (data, type, row) => {
          return {
            all: 'Tous les utilisateurs',
            collab: 'Collaborateurs uniquement',
            partner: 'Partenaires uniquement',
          }[data];
        },
      },
      {
        data: 'content',
        name: 'content',
        searchable: false,
        render: (data, type, row) => {
          return truncate($(data).text().trim());
        },
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/broadcast-messages/list`,
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
