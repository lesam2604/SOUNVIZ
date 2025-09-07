let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-categories/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-categories';
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

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-categories/store`,
      type: 'POST',
      data: {
        name: $('#name').val(),
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
      url: `${API_BASEURL}/inv-categories/update/${object.id}`,
      type: 'POST',
      data: {
        name: $('#name').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/inv-categories/${object.id}`;
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
      ? `Édition de la catégorie de produits ${object.code}`
      : 'Nouvelle catégorie de produits'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
