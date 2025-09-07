let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/broadcast-messages/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/broadcast-messages';
    }
  }
}

function displayObject() {
  $('#label').html(object.label);
  $('#content').html(`
    <div class="ql-container">
      <div class="ql-editor">${object.content}</div>
    </div>
  `);
  $('#group').html(
    {
      all: 'Tous les utilisateurs',
      collab: 'Collaborateurs uniquement',
      partner: 'Partenaires uniquement',
    }[object.group]
  );
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer le message de diffusion ${object.label}?`,
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
      url: `${API_BASEURL}/broadcast-messages/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/broadcast-messages';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function markAsSeen() {
  try {
    await ajax({
      url: `${API_BASEURL}/broadcast-messages/mark-as-seen/${object.id}`,
      type: 'POST',
    });
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(`Details du message de diffusion ${object.label}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/broadcast-messages/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });

  markAsSeen();
};
