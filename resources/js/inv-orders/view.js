let object = null;
let datatableProducts = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-orders/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-orders';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#clientFirstName').html(object.client_first_name);
  $('#clientLastName').html(object.client_last_name);

  for (const product of object.products) {
    datatableProducts.row.add({
      product_id: product.id,
      code: product.code,
      name: product.name,
      category_name: product.category.name,
      unit_price: product.pivot.unit_price,
      quantity: product.pivot.quantity,
      total_cost: product.pivot.unit_price * product.pivot.quantity,
    });
  }

  datatableProducts.draw();
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer la commande ${object.code}?`,
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
      url: `${API_BASEURL}/inv-orders/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/inv-orders';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

function initDataTableProducts() {
  datatableProducts = $('#tableProducts').DataTable({
    columns: [
      {
        data: 'code',
      },
      {
        data: 'name',
      },
      {
        data: 'category_name',
      },
      {
        data: 'unit_price',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
      {
        data: 'quantity',
      },
      {
        data: 'total_cost',
        render: (data, type, row) => {
          return formatAmount(data);
        },
      },
    ],
    order: [0, 'desc'],
    pageLength: 25,
    autoWidth: false,
  });

  $('#tableProducts').wrap('<div style="overflow-x: auto;"></div>');
}

window.render = async function () {
  await fetchObject();
  initDataTableProducts();

  setTitle(`Details de la commande ${object.code}`);
  $('#titleProducts').html(`Liste des produits sur la commande ${object.code}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/inv-orders/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });

  // Validation facture: accessible admin/collab côté UI
  async function approveInvoice(isPaid) {
    try {
      swalLoading();
      const { data } = await ajax({
        url: `${API_BASEURL}/inv-orders/approve/${object.id}`,
        type: 'POST',
        data: { is_paid: isPaid ? 'true' : 'false' },
      });
      Toast.fire(data.message || 'Commande validée.', '', 'success');
      location.reload();
    } catch ({ error }) {
      Swal.fire(error?.responseJSON?.message || 'Erreur', '', 'error');
    }
  }

  $('#approvePaid').on('click', () => approveInvoice(true));
  $('#approveUnpaid').on('click', () => approveInvoice(false));
};
