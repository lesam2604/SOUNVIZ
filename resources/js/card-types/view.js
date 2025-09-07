let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/card-types/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/card-types';
    }
  }
}

function displayObject() {
  $('#name').html(object.name);
  $('#createdAt').html(formatDateTime(object.created_at));
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer le type de carte ${object.name}?`,
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
      url: `${API_BASEURL}/card-types/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/card-types';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(`Détails du type de carte ${object.name}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/card-types/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });
};
