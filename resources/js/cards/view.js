let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/cards/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/cards';
    }
  }
}

function displayObject() {
  $('#cardId').html(object.card_id);
  $('#category').html(object.category.name);
  $('#sold').html(object.card_order_id === null ? 'Non' : 'Oui');

  const clientIsPartner = !!object.order.partner;

  $('#codeClient').html(
    clientIsPartner
      ? `<a href="/partners/${object.order.partner_id}">${object.order.partner.user.code}</a>`
      : `<a href="/extra-clients/${object.order.extra_client_id}">${object.order.extra_client.code}</a>`
  );

  $('#lastName').html(
    clientIsPartner
      ? object.order.partner.user.last_name
      : object.order.extra_client.last_name
  );

  $('#firstName').html(
    clientIsPartner
      ? object.order.partner.user.first_name
      : object.order.extra_client.first_name
  );

  $('#companyName').html(
    clientIsPartner
      ? object.order.partner.company.name
      : object.order.extra_client.company_name
  );

  $('#tin').html(
    clientIsPartner
      ? object.order.partner.company.tin
      : object.order.extra_client.tin
  );

  $('#phoneNumber').html(
    clientIsPartner
      ? object.order.partner.user.phone_number
      : object.order.extra_client.phone_number
  );
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer la carte ${object.card_id}?`,
      text: 'Cette opération est irreversible',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });

    if (!swalResult.isConfirmed) {
      throw {};
    }

    swalLoading();

    let { data } = await ajax({
      url: `${API_BASEURL}/cards/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/cards';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(`Details de la carte ${object.card_id}`);

  displayObject();

  $('#edit').click(function (e) {
    location = `/cards/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });
};
