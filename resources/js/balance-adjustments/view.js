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

function displayObject() {
  $('#code').html(object.code);
  $('#partner').html(
    object.partner.user.first_name + ' ' + object.partner.user.last_name
  );
  $('#codePartner').html(object.partner.user.code);
  $('#old_balance').html(formatAmount(object.old_balance));
  $('#balance').html(formatAmount(object.balance));
  $('#reason').html(object.reason);
  $('#createdAt').html(formatDateTime(object.created_at));
}

window.render = async function () {
  await fetchObject();
  setTitle(`Details de l'ajustement ${object.code}`);
  displayObject();
};
