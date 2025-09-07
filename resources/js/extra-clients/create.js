let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/extra-clients/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/extra-clients';
    }
  }
}

function clearForm() {
  $('#companyName').val('');
  $('#tin').val('');
  $('#phoneNumber').val('');
  $('#firstName').val('');
  $('#lastName').val('');
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#companyName').val(object.company_name);
  $('#tin').val(object.tin);
  $('#phoneNumber').val(object.phone_number);
  $('#firstName').val(object.first_name);
  $('#lastName').val(object.last_name);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/extra-clients/store`,
      type: 'POST',
      data: {
        company_name: $('#companyName').val(),
        tin: $('#tin').val(),
        phone_number: $('#phoneNumber').val(),
        first_name: $('#firstName').val(),
        last_name: $('#lastName').val(),
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
      company_name: '#companyName',
      tin: '#tin',
      phone_number: '#phoneNumber',
      first_name: '#firstName',
      last_name: '#lastName',
    });
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/extra-clients/update/${object.id}`,
      type: 'POST',
      data: {
        company_name: $('#companyName').val(),
        tin: $('#tin').val(),
        phone_number: $('#phoneNumber').val(),
        first_name: $('#firstName').val(),
        last_name: $('#lastName').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/extra-clients/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      company_name: '#companyName',
      tin: '#tin',
      phone_number: '#phoneNumber',
      first_name: '#firstName',
      last_name: '#lastName',
    });
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(
    object ? `Édition du client extra ${object.code}` : 'Nouveau client extra'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
