let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/inv-orders/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/inv-orders';
    }
  }
}

function clearForm() {
  $('#clientFirstName').val('');
  $('#clientLastName').val('');
  clearProductAdding();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#clientFirstName').val(object.client_first_name);
  $('#clientLastName').val(object.client_last_name);
  setProductAdding(object.products);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-orders/store`,
      type: 'POST',
      data: fillProductAdding({
        client_first_name: $('#clientFirstName').val(),
        client_last_name: $('#clientLastName').val(),
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
      client_first_name: '#clientFirstName',
      client_last_name: '#clientLastName',
    });
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/inv-orders/update/${object.id}`,
      type: 'POST',
      data: fillProductAdding({
        client_first_name: $('#clientFirstName').val(),
        client_last_name: $('#clientLastName').val(),
      }),
    });

    Toast.fire(data.message, '', 'success');
    location = `/inv-orders/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      client_first_name: '#clientFirstName',
      client_last_name: '#clientLastName',
    });
  }
}

window.render = async function () {
  await Promise.all([fetchObject(), populateProductAdding()]);
  initProductAdding();

  setTitle(
    object ? `Édition de la commande ${object.code}` : 'Nouvelle commande'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
