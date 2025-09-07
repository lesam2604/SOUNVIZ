function clearForm() {
  $('#partnerId').val('').change();
  clearCardAddingTypes();
  $('.is-invalid').removeClass('is-invalid');
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/card-orders/store`,
      type: 'POST',
      data: fillCardAddingTypes({
        client_type: $('#clientType').val(),
        partner_id: $('#partnerId').val(),
        extra_client_id: $('#extraClientId').val(),
      }),
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(
      error.responseJSON,
      fillCardAddingErrorFields({
        client_type: '#clientType',
        partner_id: '#partnerId',
        extra_client_id: '#extraClientId',
      })
    );
  }
}

window.render = async function () {
  $('#clientType')
    .change(function () {
      switch ($(this).val()) {
        case 'partner':
          $('#partnerId').parent().show();
          $('#extraClientId').parent().hide();
          break;

        case 'extra_client':
          $('#partnerId').parent().hide();
          $('#extraClientId').parent().show();
          break;
      }
    })
    .change();

  populatePartners('#partnerId');
  populateExtraClients('#extraClientId');
  initCardAddingTypes();
  setTitle('Nouvelle commande de carte');

  clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    createObject();
  });
};
