let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-products/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-products';
    }
  }
}

async function populateCategories() {
  try {
    let { data: categories } = await ajax({
      url: `${API_BASEURL}/inv-categories/fetch-all`,
      type: 'GET',
    });

    $('#categoryId').empty();

    for (const category of categories) {
      $('#categoryId').append(
        `<option value="${category.id}">${category.name}</option>`
      );
    }
  } catch ({ error }) {
    await Swal.fire(error.responseJSON.message, '', 'error');
    location = '/inv-products';
  }
}

function clearForm() {
  $('#name').val('');
  $('#unitPrice').val('0');
  $('#categoryId').val('');
  $('#stockQuantity').val('0');
  $('#stockQuantityMin').val('0');
  $('#picture').val('');
  $('.update-image-helper').hide();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#name').val(object.name);
  $('#unitPrice').val(object.unit_price);
  $('#categoryId').val(object.category_id).prop('disabled', true);
  $('#stockQuantity').val(object.stock_quantity).prop('disabled', true);
  $('#stockQuantityMin').val(object.stock_quantity_min);
  $('#picture').val('');
}

async function createObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('name', $('#name').val());
  formData.append('unit_price', $('#unitPrice').val());
  formData.append('category_id', $('#categoryId').val());
  formData.append('stock_quantity', $('#stockQuantity').val());
  formData.append('stock_quantity_min', $('#stockQuantityMin').val());
  formData.append('picture', $('#picture')[0].files[0] ?? '');

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-products/store`,
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
      category_id: '#categoryId',
      stock_quantity: '#stockQuantity',
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
      url: `${API_BASEURL}/inv-products/update/${object.id}`,
      type: 'POST',
      contentType: false,
      processData: false,
      data: formData,
    });

    Toast.fire(data.message, '', 'success');
    location = `/inv-products/${object.id}`;
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
  await Promise.all([fetchObject(), populateCategories()]);
  setTitle(object ? `Édition du produit ${object.code}` : 'Nouveau produit');

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
