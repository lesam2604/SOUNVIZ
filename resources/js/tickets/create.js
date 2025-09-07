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

function clearForm() {
  $('#issue').val('');
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#issue').val(object.issue);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/tickets/store`,
      type: 'POST',
      data: {
        issue: $('#issue').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON);
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/tickets/update/${object.id}`,
      type: 'POST',
      data: {
        issue: $('#issue').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/tickets/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON);
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(
    object
      ? `Édition de l'assistance service ${object.code}`
      : "Nouvelle assistance service"
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
