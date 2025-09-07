let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/partners/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/partners';
    }
  }
}

function displayObject() {
  $('#code').html(object.user.code);
  $('#firstName').html(object.user.first_name);
  $('#lastName').html(object.user.last_name);
  $('#phoneNumber').html(object.user.phone_number);
  $('#email').html(object.user.email);
  $('#picture').html(
    `<img src="${getUploadUrl(object.user.picture)}" width="360">`
  );
  $('#idCardNumber').html(object.idcard_number);
  $('#idCardPicture').html(
    `<img src="${getUploadUrl(object.idcard_picture)}" width="360">`
  );
  $('#address').html(object.address);
  $('#companyName').html(object.company.name);
  $('#tin').html(object.company.tin);
  $('#status').html(
    {
      pending: `<span class="badge rounded-pill bg-secondary">En attente</span>`,
      enabled: `<span class="badge rounded-pill bg-success">Actif</span>`,
      disabled: `<span class="badge rounded-pill bg-secondary">Inactif</span>`,
      rejected: `<span class="badge rounded-pill bg-danger">Rejeté</span>`,
    }[object.user.status]
  );
  $('#reviewedAt').html(object.user.reviewed_at);
  $('#feedback').html(object.user.feedback);

  if (!object.is_master) {
    $('#opTypesCard').hide();
  } else {
    let tBody = $('#opTypesTable tbody');

    for (const opt of object.master.operation_types) {
      let tr = $(`
        <tr>
          <td>
            ${opt.operation_type.name}
            ${opt.card_type ? `(${opt.card_type})` : ''}
          </td>
          <td>
            ${
              opt.has_commissions
                ? `<span class="badge rounded-pill bg-success has-commissions">Actif</span>`
                : `<span class="badge rounded-pill bg-secondary has-commissions">Inactif</span>`
            }
          </td>
          <td>
            ${
              opt.has_commissions
                ? `
                <button type="button" class="btn btn-sm btn-outline-danger disable-status-commissions">
                  <i class="fas me-2 fa-ban"></i> Désactiver les commissions
                </button>`
                : `
                <button type="button" class="btn btn-sm btn-outline-success enable-status-commissions">
                  <i class="fas me-2 fa-check"></i> Activer les commissions
                </button>`
            }
          </td>
        </tr>
      `).data({
        operationType: opt.operation_type,
        cardType: opt.card_type,
      });

      tBody.append(tr);
    }
  }
}

async function approveObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment valider le compte ${object.user.code}?`,
      text: 'Cette opération est irréversible',
      input: 'textarea',
      inputPlaceholder: 'Vous pouvez laisser un feedback ici...',
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
      url: `${API_BASEURL}/partners/approve/${object.id}`,
      type: 'POST',
      data: {
        feedback: swalResult.value,
      },
    });

    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function rejectObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment rejeter le compte ${object.user.code}?`,
      text: 'Cette opération est irreversible',
      input: 'textarea',
      inputPlaceholder: 'Vous pouvez laisser un feedback ici...',
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
      url: `${API_BASEURL}/partners/reject/${object.id}`,
      type: 'POST',
      data: {
        feedback: swalResult.value,
      },
    });

    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function changeStatusObject(status) {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment ${
        status === 'enabled' ? 'activer' : 'désactiver'
      } le compte ${object.user.code}?`,
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
      url: `${API_BASEURL}/partners/change-status/${object.id}`,
      type: 'POST',
      data: { status },
    });

    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function changeCommissionStatusObject(operationType, cardType, status) {
  try {
    let swalResult = await Swal.fire({
      title: `
        Voulez-vous vraiment
        ${status === 'enabled' ? 'activer' : 'désactiver'}
        les commissions du compte ${object.user.code} pour
        ${operationType.name}${cardType ? ` (${cardType})` : ''}?
      `,
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
      url: `${API_BASEURL}/partners/change-commissions-status/${object.id}`,
      type: 'POST',
      data: {
        operation_type_id: operationType.id,
        card_type: cardType,
        status,
      },
    });

    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    console.log(error);
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function deleteObject() {
  try {
    let swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer le compte ${object.user.code}?`,
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
      url: `${API_BASEURL}/partners/delete/${object.id}`,
      type: 'POST',
    });

    Toast.fire(data.message, '', 'success');
    location = '/partners';
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = async function () {
  await fetchObject();

  setTitle(
    object.is_master
      ? `Détails du partenaire ${object.user.code}`
      : `Détails de la boutique ${object.user.code}`
  );

  $('#titleOpTypes').html(
    `Statut des commissions du partenaire ${object.user.code}`
  );

  displayObject();

  $('#opTypesTable').on('click', '.enable-status-commissions', function (e) {
    changeCommissionStatusObject(
      $(this).closest('tr').data('operationType'),
      $(this).closest('tr').data('cardType'),
      'enabled'
    );
  });

  $('#opTypesTable').on('click', '.disable-status-commissions', function (e) {
    changeCommissionStatusObject(
      $(this).closest('tr').data('operationType'),
      $(this).closest('tr').data('cardType'),
      'disabled'
    );
  });

  $('#edit').click(function (e) {
    location = `/partners/${object.id}/edit`;
  });

  $('#approve').click(function (e) {
    approveObject();
  });

  $('#reject').click(function (e) {
    rejectObject();
  });

  $('#enable').click(function (e) {
    changeStatusObject('enabled');
  });

  $('#disable').click(function (e) {
    changeStatusObject('disabled');
  });

  $('#delete').click(function (e) {
    deleteObject();
  });

  if (object.user.status !== 'pending') {
    $('#approve').hide();
    $('#reject').hide();
  }

  if (object.user.status !== 'disabled') {
    $('#enable').hide();
  }

  if (object.user.status !== 'enabled') {
    $('#disable').hide();
  }
};
