let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/extra-clients/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/extra-clients';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#companyName').html(object.company_name);
  $('#tin').html(object.tin);
  $('#phoneNumber').html(object.phone_number);
  $('#firstName').html(object.first_name);
  $('#lastName').html(object.last_name);
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer le client extra ${object.code}?`,
      html: `
        <span class="text-danger fw-bold">
          Cela supprimera également définitivement toutes les opérations effectués par ce client extra
        </span>
      `,
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
      url: `${API_BASEURL}/extra-clients/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/extra-clients';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(`Details du client extra ${object.code}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/extra-clients/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });
};
