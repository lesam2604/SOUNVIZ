let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/decoders/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/decoders';
    }
  }
}

function clearForm() {
  window.clearDecoderAddingTypes();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#type').val('one').change().parent().hide();
  $('#decoderNumber').val(object.decoder_number);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/decoders/store`,
      type: 'POST',
      data: window.fillDecoderAddingTypes({}),
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, window.fillDecoderAddingErrorFields({}));
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/decoders/update/${object.id}`,
      type: 'POST',
      data: {
        decoder_number: $('#decoderNumber').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/decoders/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    viewErrors(error.responseJSON.errors);
  }
}

window.render = async function () {
  await fetchObject();

  initDecoderAddingTypes();

  setTitle(
    object
      ? `Édition du décodeur ${object.decoder_number}`
      : 'Nouveaux décodeurs'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
