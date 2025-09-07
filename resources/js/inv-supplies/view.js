let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-supplies/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-supplies';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#product').html(object.product.name);
  $('#category').html(object.product.category.name);
  $('#quantity').html(object.quantity);
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer l'approvisionnement ${object.code}?`,
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
      url: `${API_BASEURL}/inv-supplies/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/inv-supplies';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();
  setTitle(`Details de l'approvisionnement ${object.code}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/inv-supplies/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });
};
