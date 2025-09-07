let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/tickets/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/tickets';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#issue').html(object.issue);
  $('#response').html(object.response);
  $('#responder').html(
    object.responder === null
      ? ''
      : `${object.responder.first_name}  ${object.responder.last_name}`
  );
  $('#respondedAt').html(object.responded_at);
}

async function respondObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/tickets/respond/${object.id}`,
      type: 'POST',
      data: {
        response: $('#ourResponse').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      response: '#ourResponse',
    });
  }
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer l'assistance service ${object.code}?`,
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
      url: `${API_BASEURL}/tickets/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/tickets';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();
  setTitle(`Details de l'assistance service ${object.code}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/tickets/${object.id}/edit`;
  });

  $('#respond').click(function (e) {
    $('#modalRespond').modal('show');
  });

  $('#submitResponse').click(function (e) {
    respondObject();
  });

  $('#delete').click(function (e) {
    deleteObject();
  });

  if (object.response !== null) {
    $('#edit').hide();
    $('#respond').hide();
    $('#delete').hide();
  }
};
