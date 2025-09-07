/* resources/js/operations/view.js (clean) */

let object = null;
let opType = null;

function fetchOpType() {
  const opTypeCode = $('#opTypeCode').val();
  opType = (SETTINGS && Array.isArray(SETTINGS.opTypes))
    ? SETTINGS.opTypes.find((o) => o.code === opTypeCode)
    : null;
}

async function fetchObject() {
  const objectId = $('#objectId').val();
  if (!objectId || !opType) return;
  try {
    const { data } = await ajax({
      url: `${API_BASEURL}/operations/${opType.code}/fetch/${objectId}`,
      type: 'GET',
    });
    object = data;
  } catch ({ error }) {
    await Swal.fire(error?.responseJSON?.message || 'Erreur', '', 'error');
    location = `/operations/${opType?.code || ''}`;
  }
}

function displayObject() {
  if (!object || !opType) return;

  if (['account_recharge', 'balance_withdrawal'].includes(opType.code)) {
    $('#blockCommissions').hide();
  } else {
    const amount = parseInt(object.amount || 0); // net amount (after fee)
    const fee = parseInt(object.fee || 0);
    const commission = parseInt(object.commission || 0);
    const platform = parseInt((object.data && object.data.platform_commission) || 0);

    // Input amount originally entered by user: prefer stored field value if available, fallback to net+fee
    let inputAmount = amount + fee;
    try {
      if (opType && opType.amount_field && object.data && object.data[opType.amount_field] != null) {
        const v = parseInt(object.data[opType.amount_field]);
        if (!isNaN(v) && v > 0) inputAmount = v;
      }
    } catch (_) {}

    $('#amount').html(formatAmount(inputAmount));
    $('#fee').html(formatAmount(fee));
    $('#totalAmount').html(formatAmount(inputAmount + fee + (platform || 0)));
    $('#commission').html(formatAmount(commission));
    $('#commissionPlatform').html(formatAmount(platform || (fee > commission ? fee - commission : 0)));
  }

  if (object.partner?.user) {
    $('#codePartner').html(object.partner.user.code || '');
    $('#firstName').html(object.partner.user.first_name || '');
    $('#lastName').html(object.partner.user.last_name || '');
  }
  if (object.company) {
    $('#companyName').html(object.company.name || '');
    $('#tin').html(object.company.tin || '');
  }

  switch (object.status) {
    case 'pending':
      $('#status').html('<span class="badge bg-secondary">En attente</span>');
      break;
    case 'approved':
      $('#status').html('<span class="badge bg-success">Validée</span>');
      break;
    case 'rejected':
      $('#status').html('<span class="badge bg-danger">Rejetée</span>');
      break;
  }

  $('#reviewer').html(
    object.status === 'pending' ? '' : `${object.reviewer?.first_name || ''} ${object.reviewer?.last_name || ''}`
  );
  $('#reviewedAt').html(object.status === 'pending' ? '' : (object.reviewed_at || ''));
  $('#feedback').html(object.feedback || '');

  const tBody = $('#tableOperation tbody');
  tBody.html('');
  for (const [fieldName, fieldData] of (opType.sorted_fields || [])) {
    if (fieldName === opType.amount_field) continue;
    let content = '';
    const v = object.data ? object.data[fieldName] : null;
    if (v != null) {
      switch (fieldData.type) {
        case 'number':
          content = fieldData.is_amount ? formatAmount(v) : v;
          break;
        case 'file':
          content = `<a class="btn btn-outline-primary" target="_blank" href="${getUploadUrl(v)}"><i class="fas fa-eye"></i></a>`;
          break;
        case 'country':
          content = `<img src="${getCountryFlagUrl(object.countries[fieldName].code)}"> ${object.countries[fieldName].name}`;
          break;
        default:
          content = v;
      }
    }
    if (opType.code === 'card_recharge' && ['client_first_name', 'client_last_name'].includes(fieldName)) {
      content = `<span class="fw-bold text-danger">${content}</span>`;
    }
    tBody.append(`<tr><th>${fieldData.label}</th><td>${content}</td></tr>`);
  }

  if (USER?.hasRole?.('reviewer') && opType.code === 'card_activation' && object.duplicates) {
    $('#alertMessage')
      .html(
        `Ce client a déjà fait l'objet ${
          object.duplicates === 1 ? `d'une autre activation de carte approuvée` : `de ${object.duplicates} autres activations de cartes approuvées`
        }`
      )
      .show();
  }
}

async function approveObject() {
  try {
    const swalResult = await Swal.fire({
      title: `Voulez-vous vraiment valider l'opération ${opType.name} ${object.code}?`,
      text: 'Cette opération est irréversible',
      input: 'textarea',
      inputPlaceholder: 'Vous pouvez laisser un feedback ici...',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });
    if (!swalResult.isConfirmed) throw {};
    swalLoading();
    const { data } = await ajax({
      url: `${API_BASEURL}/operations/${opType.code}/approve/${object.id}`,
      type: 'POST',
      data: { feedback: swalResult.value, without_commission: false },
    });
    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error?.responseJSON?.message || 'Erreur', '', 'error');
  }
}

async function approveObjectWithoutCommission() {
  try {
    const swalResult = await Swal.fire({
      title: `Voulez-vous vraiment valider l'opération ${opType.name} ${object.code} sans les commissions?`,
      text: 'Cette opération est irréversible. Et le partenaire ne recevra pas de commissions.',
      input: 'textarea',
      inputPlaceholder: 'Vous pouvez laisser un feedback ici...',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });
    if (!swalResult.isConfirmed) throw {};
    swalLoading();
    const { data } = await ajax({
      url: `${API_BASEURL}/operations/${opType.code}/approve/${object.id}`,
      type: 'POST',
      data: { feedback: swalResult.value, without_commission: true },
    });
    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error?.responseJSON?.message || 'Erreur', '', 'error');
  }
}

async function rejectObject() {
  try {
    const swalResult = await Swal.fire({
      title: `Voulez-vous vraiment rejeter l'opération ${opType.name} ${object.code}?`,
      text: 'Cette opération est irréversible',
      input: 'textarea',
      inputPlaceholder: 'Vous pouvez laisser un feedback ici...',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });
    if (!swalResult.isConfirmed) throw {};
    swalLoading();
    const { data } = await ajax({
      url: `${API_BASEURL}/operations/${opType.code}/reject/${object.id}`,
      type: 'POST',
      data: { feedback: swalResult.value },
    });
    Toast.fire(data.message, '', 'success');
    location.reload();
  } catch ({ error }) {
    Swal.fire(error?.responseJSON?.message || 'Erreur', '', 'error');
  }
}

async function deleteObject() {
  try {
    const swalResult = await Swal.fire({
      title: `Voulez-vous vraiment supprimer l'opération ${opType.name} ${object.code}?`,
      text: 'Cette opération est irréversible',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui',
      cancelButtonText: 'Non',
    });
    if (!swalResult.isConfirmed) throw {};
    swalLoading();
    const { data } = await ajax({ url: `${API_BASEURL}/operations/${opType.code}/delete/${object.id}`, type: 'POST' });
    Toast.fire(data.message, '', 'success');
    location = `/operations/${opType.code}`;
  } catch ({ error }) {
    Swal.fire(error?.responseJSON?.message || 'Erreur', '', 'error');
  }
}

function addCancelButtonIfAllowed() {
  const isCollab = typeof USER?.hasRole === 'function' && USER.hasRole('collab');
  const isApproved = !!object && object.status === 'approved';
  const $actionsCardBody = $('.card-header h4')
    .filter(function () { return $(this).text().trim() === 'Actions'; })
    .closest('.card')
    .find('.card-body')
    .first();
  if (!isCollab || !isApproved || !$actionsCardBody.length) return;
  if (!$('#btnCancelOp').length) {
    const $btn = $(`<button type="button" class="btn btn-lg btn-outline-danger ms-2" id="btnCancelOp"><i class="fas fa-undo me-2"></i> Demander l'annulation</button>`);
    $actionsCardBody.append($btn);
    $('#btnCancelOp').on('click', async function () {
      const csrf = $('meta[name="csrf-token"]').attr('content') || '';
      const { value: reason } = await Swal.fire({
        title: "Demander l'annulation",
        input: 'textarea',
        inputPlaceholder: 'Motif (obligatoire)',
        showCancelButton: true,
        confirmButtonText: 'Envoyer',
        cancelButtonText: 'Annuler',
        preConfirm: (val) => { if (!val || !val.trim()) { Swal.showValidationMessage('Le motif est requis'); } return val; }
      });
      if (!reason) return;
      Swal.fire({ title: 'Envoi en cours...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      try {
        const res = await $.ajax({ url: `${API_BASEURL}/operations-cancel/request/${object.id}` , method: 'POST', dataType: 'json', data: { reason }, headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } });
        Swal.fire(res.message || 'Demande envoyée.', '', 'success');
        $('#btnCancelOp').prop('disabled', true);
      } catch (err) {
        const msg = err?.responseJSON?.message || err?.responseText || err?.message || 'Erreur';
        Swal.fire(msg, '', 'error');
      }
    });
  }
}

window.render = async function () {
  fetchOpType();
  await fetchObject();
  setTitle(`${opType.name} ${object.code}`);
  $('#linkList').html(`<i class="fas fa-list"></i> Liste des opérations`).attr('href', `/operations/${opType.code}`);
  displayObject();
  addCancelButtonIfAllowed();

  if (object && object.status === 'approved') {
    $('#createInvoice').show().off('click').on('click', function () {
      try {
        const partnerId = (object.partner && object.partner.id) ? object.partner.id : object.partner_id;
        if (!partnerId) throw new Error('Partenaire introuvable');
        const partnerText = (object.partner && object.partner.user)
          ? `${object.partner.user.code} - ${object.partner.user.first_name} ${object.partner.user.last_name}${object.company?.name ? ' ('+object.company.name+')' : ''}`
          : `${partnerId}`;
        const params = new URLSearchParams();
        params.set('opTypeCode', opType.code);
        params.set('clientType', 'partner');
        params.set('partnerId', String(partnerId));
        params.set('partnerText', partnerText);
        params.set('lock', '1');
        // Préremplir la désignation avec le type d'opération; PU restera modifiable
        const defaultItems = [{ label: opType.name || 'Opération', qty: 1, unit: 0 }];
        params.set('items', JSON.stringify(defaultItems));
        // NE PAS envoyer de montant: l'utilisateur le renseigne manuellement
        location = `/invoices/create?${params.toString()}`;
      } catch (err) {
        const msg = err?.message || 'Impossible d’ouvrir la création de facture';
        if (typeof Swal !== 'undefined') Swal.fire(msg, '', 'error'); else alert(msg);
      }
    });
  } else { $('#createInvoice').hide(); }

  $('#edit').click(() => (location = `/operations/${opType.code}/${object.id}/edit`));
  $('#approve').click(approveObject);
  $('#approveWithoutCommission').click(approveObjectWithoutCommission);
  $('#reject').click(rejectObject);
  $('#delete').click(deleteObject);

  if (object.status !== 'rejected') $('#edit').hide();
  if (object.status !== 'pending') { $('#approve').hide(); $('#reject').hide(); }
  if (
    object.status !== 'pending' ||
    ['account_recharge','balance_withdrawal'].includes(opType.code) ||
    (!hasCommissions(object.master, opType.id, object.data.card_type || null) && opType.code !== 'card_activation')
  ) { $('#approveWithoutCommission').hide(); }
};
