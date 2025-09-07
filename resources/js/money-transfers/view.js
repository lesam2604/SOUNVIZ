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

function displayObject() {
  $('#code').html(object.code);
  $('#sender').html(
    object.sender.user.first_name + ' ' + object.sender.user.last_name
  );
  $('#senderCode').html(object.sender.user.code);
  $('#recipient').html(
    object.recipient.user.first_name + ' ' + object.recipient.user.last_name
  );
  $('#recipientCode').html(object.recipient.user.code);
  $('#amount').html(formatAmount(object.amount));
}

window.render = async function () {
  await fetchObject();
  setTitle(`Details du transfert ${object.code}`);
  displayObject();
};
