async function createObject() {
  let formData = new FormData();
  formData.append('first_name', $('#firstName').val());
  formData.append('last_name', $('#lastName').val());
  formData.append('phone_number', $('#phoneNumber').val());
  formData.append('email', $('#email').val());
  formData.append('email_confirmation', $('#confirmEmail').val());
  formData.append('idcard_number', $('#idCardNumber').val());
  formData.append('address', $('#address').val());
  formData.append('picture', $('#picture')[0].files[0] ?? '');
  formData.append('idcard_picture', $('#idCardPicture')[0].files[0] ?? '');
  formData.append('company_name', $('#companyName').val());
  formData.append('tin', $('#tin').val());

  $('#form button')
    .html('Inscription en cours <i class="fas fa-spinner fa-pulse"></i>')
    .prop('disabled', true);

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/partners/register`,
      type: 'POST',
      contentType: false,
      processData: false,
      data: formData,
    });

    await Swal.fire(data.message, '', 'success');
    location.href = `/login`;
  } catch ({ error }) {
    $('#form button').html("S'inscrire").prop('disabled', false);

    console.log(error);

    showErrors(error.responseJSON, {
      first_name: '#firstName',
      last_name: '#lastName',
      phone_number: '#phoneNumber',
      email: '#email',
      email_confirmation: '#confirmEmail',
      idcard_number: '#idCardNumber',
      address: '#address',
      picture: '#picture',
      idcard_picture: '#idCardPicture',
      company_name: '#companyName',
      tin: '#tin',
    });
  }
}

window.render = function () {
  $('#form').submit(function (e) {
    e.preventDefault();
    createObject();
  });
};
