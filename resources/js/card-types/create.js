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

function clearForm() {
  $('#name').val('');
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#name').val(object.name);
}

async function createObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('name', $('#name').val());

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/card-types/store`,
      type: 'POST',
      contentType: false,
      processData: false,
      data: formData,
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      name: '#name',
    });
  }
}

async function updateObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('name', $('#name').val());

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/card-types/update/${object.id}`,
      type: 'POST',
      contentType: false,
      processData: false,
      data: formData,
    });

    Toast.fire(data.message, '', 'success');
    location = `/card-types/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      name: '#name',
    });
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(
    object ? `Édition du type de carte ${object.name}` : 'Nouveau type de carte'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
