let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/card-categories/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/card-categories';
    }
  }
}

function clearForm() {
  $('#name').val('');
  $('#unitPrice').val('');
  $('#stockQuantityMin').val('');
  $('#picture').val('');
  $('.update-image-helper').hide();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#name').val(object.name);
  $('#unitPrice').val(object.unit_price);
  $('#stockQuantityMin').val(object.stock_quantity_min);
  $('#picture').val('');
}

async function createObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('name', $('#name').val());
  formData.append('unit_price', $('#unitPrice').val());
  formData.append('stock_quantity_min', $('#stockQuantityMin').val());
  formData.append('picture', $('#picture')[0].files[0] ?? '');

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/card-categories/store`,
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
      unit_price: '#unitPrice',
      stock_quantity_min: '#stockQuantityMin',
      picture: '#picture',
    });
  }
}

async function updateObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('name', $('#name').val());
  formData.append('unit_price', $('#unitPrice').val());
  formData.append('stock_quantity_min', $('#stockQuantityMin').val());
  formData.append('picture', $('#picture')[0].files[0] ?? '');

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/card-categories/update/${object.id}`,
      type: 'POST',
      contentType: false,
      processData: false,
      data: formData,
    });

    Toast.fire(data.message, '', 'success');
    location = `/card-categories/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      name: '#name',
      unit_price: '#unitPrice',
      stock_quantity_min: '#stockQuantityMin',
      picture: '#picture',
    });
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(
    object
      ? `Édition de la catégorie de carte ${object.code}`
      : 'Nouvelle catégorie de carte'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
