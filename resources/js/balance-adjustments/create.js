let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/balance-adjustments/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/balance-adjustments';
    }
  }
}

function clearForm() {
  $('#partnerId').val('').change();
  $('#amountToWithdraw').val('0');
  $('#reason').val('');
  $('.is-invalid').removeClass('is-invalid');
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/balance-adjustments/store`,
      type: 'POST',
      data: {
        partner_id: $('#partnerId').val(),
        amount_to_withdraw: $('#amountToWithdraw').val(),
        reason: $('#reason').val(),
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
      partner_id: '#partnerId',
      amount_to_withdraw: '#amountToWithdraw',
      reason: '#reason',
    });
  }
}

window.render = async function () {
  await fetchObject();

  populatePartners('#partnerId');

  setTitle('Nouvel ajustement de solde');

  clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    createObject();
  });
};
