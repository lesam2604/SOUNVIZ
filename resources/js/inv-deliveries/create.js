let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-deliveries/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-deliveries';
    }
  }
}

async function populateOrders() {
  try {
    let { data: orders } = await ajax({
      url: `${API_BASEURL}/inv-orders/fetch-all`,
      type: 'GET',
    });

    $('#invOrderId').empty();

    for (const order of orders) {
      $('#invOrderId').append(`
        <option value="${order.id}" data-products="${htmlEntities(
        JSON.stringify(order.products)
      )}">
          ${order.client_first_name} ${order.client_last_name}
        </option>
      `);
    }
  } catch ({ error }) {
    await Swal.fire(error.responseJSON.message, '', 'error');
    location = '/inv-deliveries';
  }
}

function clearForm() {
  $('#invOrderId').val('');
  clearProductAdding();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#invOrderId').val(object.order_id).prop('disabled');
  setProductAdding(object.products);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-deliveries/store`,
      type: 'POST',
      data: fillProductAdding({
        order_id: $('#invOrderId').val(),
      }),
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      order_id: '#invOrderId',
    });
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-deliveries/update/${object.id}`,
      type: 'POST',
      data: fillProductAdding({}),
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

window.render = async function () {
  await Promise.all([fetchObject(), populateOrders()]);

  initProductAdding();

  $('#invOrderId').change(function () {
    populateProductAdding($(this).find('option:selected').data('products'));
  });

  setTitle(
    object ? `Édition de la livraison ${object.code}` : 'Nouvelle livraison'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
