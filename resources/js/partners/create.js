function clearForm() {
  $('#firstName').val('');
  $('#lastName').val('');
  $('#phoneNumber').val('');
  $('#email').val('');
  $('#confirmEmail').val('');
  $('#picture').val('');
  $('#idCardNumber').val('');
  $('#idCardPicture').val('');
  $('#address').val('');
  $('.update-image-helper').hide();
  $('.is-invalid').removeClass('is-invalid');
}

async function createObject() {
  swalLoading();

  let formData = new FormData();
  formData.append('first_name', $('#firstName').val());
  formData.append('last_name', $('#lastName').val());
  formData.append('phone_number', $('#phoneNumber').val());
  formData.append('email', $('#email').val());
  formData.append('email_confirmation', $('#confirmEmail').val());
  formData.append('picture', $('#picture')[0].files[0] ?? '');
  formData.append('idcard_number', $('#idCardNumber').val());
  formData.append('idcard_picture', $('#idCardPicture')[0].files[0] ?? '');
  formData.append('address', $('#address').val());

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/partners/store`,
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
      first_name: '#firstName',
      last_name: '#lastName',
      phone_number: '#phoneNumber',
      email: '#email',
      email_confirmation: '#confirmEmail',
      picture: '#picture',
      idcard_number: '#idCardNumber',
      idcard_picture: '#idCardPicture',
      address: '#address',
    });
  }
}

window.render = async function () {
  if (USER.hasRole('reviewer')) {
    setTitle('Nouveau partenaire');
  } else {
    setTitle('Nouvelle boutique');
  }

  clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    createObject();
  });
};
