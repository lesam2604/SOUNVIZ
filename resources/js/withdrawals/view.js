let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/withdrawals/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/withdrawals';
    }
  }
}

function displayObject() {
  $('#code').html(object.code);
  $('#amount').html(formatAmount(object.amount));
  $('#createdAt').html(formatDateTime(object.created_at));
}

window.render = async function () {
  await fetchObject();
  setTitle(`Details du retrait ${object.code}`);
  displayObject();
};
