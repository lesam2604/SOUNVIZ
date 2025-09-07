let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-products/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-products';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#name').html(object.name);
  $('#unitPrice').html(formatAmount(object.unit_price));
  $('#category').html(object.category.name);
  $('#stockQuantity').html(object.stock_quantity);
  $('#stockQuantityMin').html(object.stock_quantity_min);
  $('#picture').html(
    object.picture
      ? `<img src="${getUploadUrl(object.picture)}" width="360">`
      : ''
  );
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer le produit ${object.code}?`,
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
      url: `${API_BASEURL}/inv-products/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/inv-products';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();
  setTitle(`Details du produit ${object.code}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/inv-products/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });
};
