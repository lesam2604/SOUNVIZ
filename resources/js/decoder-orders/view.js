let object = null;
let dataTableDecoders = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/decoder-orders/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/decoder-orders';
    }
  }
}

function displayObject() {
  const clientIsPartner = !!object.partner;

  $('#code').html(object.code);

  $('#codeClient').html(
    clientIsPartner
      ? USER.hasRole('reviewer')
        ? `<a href="/partners/${object.partner_id}">${object.partner.user.code}</a>`
        : object.partner.user.code
      : `<a href="/extra-clients/${object.extra_client_id}">${object.extra_client.code}</a>`
  );

  $('#lastName').html(
    clientIsPartner
      ? object.partner.user.last_name
      : object.extra_client.last_name
  );

  $('#firstName').html(
    clientIsPartner
      ? object.partner.user.first_name
      : object.extra_client.first_name
  );

  $('#companyName').html(
    clientIsPartner
      ? object.partner.company.name
      : object.extra_client.company_name
  );

  $('#tin').html(
    clientIsPartner ? object.partner.company.tin : object.extra_client.tin
  );

  $('#phoneNumber').html(
    clientIsPartner
      ? object.partner.user.phone_number
      : object.extra_client.phone_number
  );

  dataTableDecoders.draw();
}

async function deleteObject() {
  let swalResult = await Swal.fire({
    title: `Voulez-vous vraiment supprimer la commande de décodeur ${object.code}?`,
    text: 'Cette opération est irreversible',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Oui',
    cancelButtonText: 'Non',
  });

  if (!swalResult.isConfirmed) return;

  try {
    swalLoading();

    let { data } = await ajax({
      url: `${API_BASEURL}/decoder-orders/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/decoder-orders';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

function initDataTableDecoders() {
  dataTableDecoders = $('#tableDecoders').DataTable({
    deferLoading: 0,
    processing: true,
    serverSide: true,
    columns: [
      {
        data: '__no__',
        name: 'id',
      },
      {
        data: 'decoder_number',
        name: 'decoder_number',
      },
    ],
    order: [0, 'desc'],
    ajax: {
      url: `${API_BASEURL}/decoder-orders/list-decoders`,
      type: 'POST',
      dataType: 'json',
      data: (d) => {
        d.decoder_order_id = object.id;
      },
      error: (error) => {
        Swal.fire(error.responseJSON.message, '', 'error');
      },
    },
    pageLength: 25,
    autoWidth: false,
  });

  $('#tableDecoders').wrap('<div style="overflow-x: auto;"></div>');
}

window.render = async function () {
  await fetchObject();

  initDataTableDecoders();

  setTitle(`Details de la commande de décodeur ${object.code}`);
  $('#titleDecoders').html(
    `Liste des décodeurs sur la commande ${object.code}`
  );

  displayObject();

  $('#edit').click(function (e) {
    location = `/decoder-orders/${object.id}/edit`;
  });

  $('#delete').click(function (e) {
    deleteObject();
  });

  $('#generateBill').click(function (e) {
    e.preventDefault();
    downloadFile(
      `${API_BASEURL}/decoder-orders/generate-bill/${object.id}`,
      'GET'
    );
  });
};
