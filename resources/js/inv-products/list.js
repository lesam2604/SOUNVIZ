let datatable = null;
let toSupply = null;

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
          return `<a href="/inv-products/${row.id}">${data}</a>`;
        },
      },
      {
        data: 'name',
        name: 'name',
      },
      {
        data: 'unit_price',
        name: 'unit_price',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'category_name',
        name: 'category_name',
      },
      {
        data: 'stock_quantity',
        name: 'stock_quantity',
      },
      {
        data: 'stock_quantity_min',
        name: 'stock_quantity_min',
      },
      {
        data: 'created_at',
        name: 'created_at',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/inv-products/list`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {
        d.to_supply = toSupply;
      },
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
  toSupply = $('#toSupply').val();
  initDataTable();
  setTitle(toSupply ? 'Produits a approvisionner' : 'Produits');
};
