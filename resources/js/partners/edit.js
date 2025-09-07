let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/partners/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
      console.log(object);
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/partners';
    }
  }
}

function setForm() {
  $('#firstName').val(object.user.first_name);
  $('#lastName').val(object.user.last_name);
  $('#phoneNumber').val(object.user.phone_number);
  $('#email').val(object.user.email);
  $('#confirmEmail').val('');
  $('#idCardNumber').val(object.idcard_number);
  $('#address').val(object.address);
  $('#companyName').val(object.company.name);
  $('#tin').val(object.company.tin);
  $('#picture').val('');
  $('#idCardPicture').val('');
}

async function updateObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('first_name', $('#firstName').val());
  formData.append('last_name', $('#lastName').val());
  formData.append('phone_number', $('#phoneNumber').val());
  formData.append('email', $('#email').val());
  formData.append('email_confirmation', $('#confirmEmail').val());
  formData.append('idcard_number', $('#idCardNumber').val());
  formData.append('address', $('#address').val());
  formData.append('company_name', $('#companyName').val());
  formData.append('tin', $('#tin').val());
  formData.append('picture', $('#picture')[0].files[0] ?? '');
  formData.append('idcard_picture', $('#idCardPicture')[0].files[0] ?? '');

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/partners/update/${object.id}`,
      type: 'POST',
      contentType: false,
      processData: false,
      data: formData,
    });

    Toast.fire(data.message, '', 'success');
    location = `/partners/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON, {
      first_name: '#firstName',
      last_name: '#lastName',
      phone_number: '#phoneNumber',
      email: '#email',
      email_confirmation: '#confirmEmail',
      idcard_number: '#idCardNumber',
      address: '#address',
      company_name: '#companyName',
      tin: '#tin',
      picture: '#picture',
      idcard_picture: '#idCardPicture',
    });
  }
}

window.render = async function () {
  await fetchObject();

  if (!object.is_master) {
    $('#companyName').parent().hide();
    $('#tin').parent().hide();
  }

  setTitle(
    object.is_master
      ? `Édition du partenaire ${object.user.code}`
      : `Édition de la boutique ${object.user.code}`
  );

  setForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    updateObject();
  });
};
