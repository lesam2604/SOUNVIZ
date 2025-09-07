let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/scrolling-messages/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/scrolling-messages';
    }
  }
}

function displayObject() {
  $('#label').html(object.label);
  $('#content').html(object.content);
  $('#from').html(object.from);
  $('#to').html(object.to);
  $('#time').html(object.time);

  $('#size').html(
    {
      small: 'Petit',
      medium: 'Moyen',
      large: 'Grand',
    }[object.size]
  );

  $('#color').html(
    {
      black: 'Noir',
      blue: 'Blue',
      red: 'Rouge',
      yellow: 'Jaune',
      green: 'Vert',
    }[object.color]
  );

  $('#show_auth').html(object.show_auth ? 'Oui' : 'Non');
  $('#show_app').html(object.show_app ? 'Oui' : 'Non');

  $('#status').html(
    {
      enabled: `<span class="badge rounded-pill bg-success">Actif</span>`,
      disabled: `<span class="badge rounded-pill bg-secondary">Inactif</span>`,
    }[object.status]
  );
}

async function changeStatusObject(status) {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment ${
        status === 'enabled' ? 'activer' : 'désactiver'
      } le message défilant ${object.label}?`,
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
      url: `${API_BASEURL}/scrolling-messages/change-status/${object.id}`,
      type: 'POST',
      data: { status: status },
    });

    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer le message défilant ${object.label}?`,
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
      url: `${API_BASEURL}/scrolling-messages/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/scrolling-messages';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();
  setTitle(`Détails du message défilant ${object.label}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/scrolling-messages/${object.id}/edit`;
  });

  $('#enable').click(function (e) {
    changeStatusObject('enabled');
  });

  $('#disable').click(function (e) {
    changeStatusObject('disabled');
  });

  $('#delete').click(function (e) {
    deleteObject();
  });

  if (object.status !== 'disabled') {
    $('#enable').hide();
  }

  if (object.status !== 'enabled') {
    $('#disable').hide();
  }
};
