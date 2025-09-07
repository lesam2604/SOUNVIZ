let object = null;
let datatableProducts = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-deliveries/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-deliveries';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#invOrderCode').html(object.order.code);
  $('#clientFirstName').html(object.order.client_first_name);
  $('#clientLastName').html(object.order.client_last_name);

  for (const product of object.products) {
    let objectOrder = object.order.products.find(
      (pro) => pro.id === product.id
    );

    datatableProducts.row.add({
      product_id: product.id,
      code: product.code,
      name: product.name,
      category_name: product.category.name,
      unit_price: objectOrder.pivot.unit_price,
      quantity: product.pivot.quantity,
      total_cost: objectOrder.pivot.unit_price * product.pivot.quantity,
    });
  }

  datatableProducts.draw();
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer la livraison ${object.code}?`,
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
      url: `${API_BASEURL}/inv-deliveries/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/inv-deliveries';
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

  setTitle(`Details de la livraison ${object.code}`);

  $('#titleProducts').html(
    `Liste des produits sur la livraison ${object.code}`
  );

  displayObject();

  $('#edit').click(function (e) {
    location = `/inv-deliveries/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });
};
