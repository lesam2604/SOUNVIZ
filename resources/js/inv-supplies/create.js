let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-supplies/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-supplies';
    }
  }
}

async function populateProducts() {
  try {
    let { data: products } = await ajax({
      url: `${API_BASEURL}/inv-products/fetch-all`,
      type: 'GET',
    });

    $('#productId').empty();

    for (const product of products) {
      $('#productId').append(
        `<option value="${product.id}">${product.name}</option>`
      );
    }
  } catch ({ error }) {
    await Swal.fire(error.responseJSON.message, '', 'error');
    location = '/inv-supplies';
  }
}

function clearForm() {
  $('#productId').val('').change();
  $('#quantity').val('0');
  $('.update-image-helper').hide();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#productId').val(object.product_id).change();
  $('#quantity').val(object.quantity);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-supplies/store`,
      type: 'POST',
      data: {
        product_id: $('#productId').val(),
        quantity: $('#quantity').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      product_id: '#productId',
      quantity: '#quantity',
    });
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-supplies/update/${object.id}`,
      type: 'POST',
      data: {
        product_id: $('#productId').val(),
        quantity: $('#quantity').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/inv-supplies/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      product_id: '#productId',
      quantity: '#quantity',
    });
  }
}

window.render = async function () {
  await Promise.all([fetchObject(), populateProducts()]);
  setTitle(
    object
      ? `Édition de l'approvisionnement ${object.code}`
      : 'Nouvel approvisionnement'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
