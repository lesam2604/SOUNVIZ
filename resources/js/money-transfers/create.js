let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/money-transfers/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/money-transfers';
    }
  }
}

function clearForm() {
  $('#recipientId').val('').change();
  $('#amount').val('0');
  $('.is-invalid').removeClass('is-invalid');
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/money-transfers/store`,
      type: 'POST',
      data: {
        recipient_id: $('#recipientId').val(),
        amount: $('#amount').val(),
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
      recipient_id: '#recipientId',
      amount: '#amount',
    });
  }
}

window.render = async function () {
  await fetchObject();
  setTitle('Nouveau transfert');

  clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    createObject();
  });

  populatePartners('#recipientId', true);
};
