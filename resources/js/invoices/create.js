async function populateOpTypes() {
  // Utilise SETTINGS.opTypes s'il est global, sinon requÃªte minimaliste
  try {
    if (typeof SETTINGS !== 'undefined' && Array.isArray(SETTINGS.opTypes)) {
      const sel = $('#opTypeCode');
      sel.html('');
      SETTINGS.opTypes.forEach(ot => sel.append(`<option value="${ot.code}">${ot.name}</option>`));
      return;
    }
  } catch {}
}

function initPartnerSelector() {
  $('#partnerId').select2({
    theme: 'bootstrap-5',
    placeholder: 'Rechercher un partenaire (code, nom, sociÃ©tÃ©)',
    allowClear: true,
    ajax: {
      transport: function (params, success, failure) {
        $.ajax({
          url: `${API_BASEURL}/partners/fetch-by-term`,
          type: 'GET',
          data: { term: params.data.term || '' },
          success,
          error: failure,
        });
      },
      delay: 250,
      processResults: function (data) {
        const results = (data || []).map(p => ({
          id: p.id,
          text: `${p.code} - ${p.first_name} ${p.last_name}${p.company_name ? ' ('+p.company_name+')' : ''}`
        }));
        return { results };
      }
    }
  });
}

function toggleClientBlocks() {
  const t = $('#clientType').val();
  if (t === 'partner') {
    $('#partnerSelectBlock').show();
    $('#extraClientSelectBlock').hide();
    $('#manualClientBlock').hide();
    $('#extraClientDetails').hide();
  } else {
    if (t === 'extra_client') {
      $('#partnerSelectBlock').hide();
      $('#extraClientSelectBlock').show();
      $('#manualClientBlock').hide();
      const ecid = $('#extraClientId').val();
      if (ecid) fetchAndShowExtraClient(ecid);
    } else {
      $('#partnerSelectBlock').hide();
      $('#extraClientSelectBlock').hide();
      $('#manualClientBlock').show();
      $('#extraClientDetails').hide();
    }
  }
}

function addItemRow(item = { label: '', qty: 1, unit: 0 }) {
  const tr = $(
    `<tr>
      <td><input type="text" class="form-control item-label" placeholder="DÃ©signation" value="${item.label}"></td>
      <td><input type="number" min="1" class="form-control item-qty" value="${item.qty}"></td>
      <td><input type="number" min="0" class="form-control item-unit" value="${item.unit}"></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-del"><i class="fas fa-trash"></i></button></td>
    </tr>`
  );
  tr.find('.btn-del').on('click', () => tr.remove());
  $('#itemsTable tbody').append(tr);
  if (window.LOCK_INVOICE_MODE) {
    tr.find('.item-label').prop('disabled', true);
    tr.find('.item-qty').prop('disabled', true);
    tr.find('.btn-del').prop('disabled', true).hide();
  }
}

function gatherItems() {
  const items = [];
  $('#itemsTable tbody tr').each(function () {
    const label = $(this).find('.item-label').val() || '';
    const qty = parseFloat($(this).find('.item-qty').val() || '0');
    const unit = parseFloat($(this).find('.item-unit').val() || '0');
    if (label && qty > 0) {
      items.push({ label, qty, unit });
    }
  });
  return items;
}

function renderExtraClientDetails(ec) {
  try {
    $('#ecCompany').text(ec.company_name || '');
    $('#ecTin').text(ec.tin || '');
    $('#ecFullName').text(`${ec.first_name || ''} ${ec.last_name || ''}`.trim());
    $('#ecPhone').text(ec.phone_number || '');
    $('#ecEmail').text(ec.email || '');
    $('#extraClientDetails').show();
  } catch (e) { console.log(e); }
}

async function fetchAndShowExtraClient(id) {
  try {
    const { data: ec } = await ajax({ url: `${API_BASEURL}/extra-clients/fetch/${id}`, type: 'GET' });
    renderExtraClientDetails(ec);
  } catch (e) {
    console.log(e);
    $('#extraClientDetails').hide();
  }
}

async function submitForm(e) {
  e.preventDefault();
  try {
    swalLoading();
    const opTypeCode = $('#opTypeCode').val();
    const clientType = $('#clientType').val();
    const payload = {
      client_type: clientType === 'extra_client' ? 'external' : clientType,
      amount: $('#amount').val(),
      items: gatherItems(),
    };
    if (clientType === 'partner') {
      const pid = $('#partnerId').val();
      if (!pid) {
        Swal.close();
        return Toast.fire('SÃ©lectionnez un partenaire', '', 'error');
      }
      payload.partner_id = pid;
    } else if (clientType === 'extra_client') {
      const ecid = $('#extraClientId').val();
      if (!ecid) {
        Swal.close();
        return Toast.fire('SÃ©lectionnez un client extra', '', 'error');
      }
      try {
        const { data: ec } = await ajax({ url: `${API_BASEURL}/extra-clients/fetch/${ecid}`, type: 'GET' });
        payload.client_name = `${ec.first_name || ''} ${ec.last_name || ''}`.trim();
        payload.client_phone = ec.phone_number || '';
        payload.client_email = ec.email || '';
      } catch (err) {
        Swal.close();
        return Toast.fire("Impossible de charger le client extra", '', 'error');
      }
    } else {
      payload.client_name = $('#client_name').val();
      payload.client_phone = $('#client_phone').val();
      payload.client_email = $('#client_email').val();
    }

    const { data } = await ajax({
      url: `${API_BASEURL}/invoices/${opTypeCode}/store`,
      type: 'POST',
      data: payload,
    });

    Toast.fire(data.message || 'Facture crÃ©Ã©e', '', 'success');
    location = `/invoices/${data.invoice.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error?.responseJSON?.errors) Swal.close();
    showErrors(error.responseJSON || { message: 'Erreur' });
  }
}

function prefillFromQuery() {
  try {
    const q = new URLSearchParams(location.search);
    const opTypeCode = q.get('opTypeCode');
    const clientType = q.get('clientType'); // 'partner' | 'extra_client' | 'external'
    const partnerId = q.get('partnerId');
    const partnerText = q.get('partnerText');
    const amount = q.get('amount');
    const items = q.get('items');
    const clientName = q.get('client_name');
    const clientPhone = q.get('client_phone');
    const clientEmail = q.get('client_email');
    const lock = q.get('lock');

    if (opTypeCode) $('#opTypeCode').val(opTypeCode);
    if (clientType) $('#clientType').val(clientType);
    toggleClientBlocks();

    if (clientType === 'partner' && partnerId) {
      const opt = new Option(partnerText || partnerId, partnerId, true, true);
      $('#partnerId').append(opt).trigger('change');
    }

    if (clientType === 'extra_client') {
      // Support futur: possibilitÃ© d'arriver avec un extraClientId prÃ©-sÃ©lectionnÃ©
      const extraClientId = q.get('extraClientId');
      const extraClientText = q.get('extraClientText');
      if (extraClientId) {
        const opt = new Option(extraClientText || extraClientId, extraClientId, true, true);
        $('#extraClientId').append(opt).trigger('change');
      }
    }

    if (clientType === 'external') {
      if (clientName) $('#client_name').val(clientName);
      if (clientPhone) $('#client_phone').val(clientPhone);
      if (clientEmail) $('#client_email').val(clientEmail);
    }

    if (amount) $('#amount').val(parseInt(amount));
    if (items) {
      try {
        const arr = JSON.parse(items);
        if (Array.isArray(arr)) {
          $('#itemsTable tbody').html('');
          arr.forEach(it => addItemRow(it));
        }
      } catch {}
    }
  } catch (e) {
    console.log(e);
  }
}

function applyLockModeIfNeeded() {
  const q = new URLSearchParams(location.search);
  const lock = q.get('lock');
  if (!lock) return;
  window.LOCK_INVOICE_MODE = true;
  $('#opTypeCode').prop('disabled', true);
  $('#clientType').prop('disabled', true);
  $('#partnerId').prop('disabled', true);
  $('#manualClientBlock :input').prop('disabled', true);
  $('#manualClientBlock').hide();
  $('#itemsTable tbody tr').each(function(){
    $(this).find('.item-label').prop('disabled', true);
    $(this).find('.item-qty').prop('disabled', true);
    $(this).find('.btn-del').prop('disabled', true).hide();
  });
}

window.render = async function () {
  await populateOpTypes();
  initPartnerSelector();
  initExtraClientSelector();
  // Reconfigure extra client selector to load full list and search by term
  if (typeof initExtraClientSelectorV2 === 'function') {
    initExtraClientSelectorV2();
  }
  $('#clientType').on('change', toggleClientBlocks);
  toggleClientBlocks();
  prefillFromQuery();
  applyLockModeIfNeeded();
  $('#extraClientId')
    .one('select2:open', async function () {
      try {
        const data = await $.ajax({ url: `${API_BASEURL}/extra-clients/fetch-all`, type: 'GET' });
        const $sel = $('#extraClientId');
        (data || []).forEach(ec => {
          if ($sel.find(`option[value='${ec.id}']`).length === 0) {
            const text = `${ec.first_name || ''} ${ec.last_name || ''}${ec.company_name ? ' ('+ec.company_name+')' : ''} - ${ec.phone_number || ''}`.trim();
            const opt = new Option(text, ec.id, false, false);
            $sel.append(opt);
          }
        });
      } catch (e) { console.log(e); }
    })
    .on('change', function () {
      if ($('#clientType').val() === 'extra_client') {
        const id = $(this).val();
        if (id) fetchAndShowExtraClient(id); else $('#extraClientDetails').hide();
      }
    });
  $('#addItemBtn').on('click', () => addItemRow());
  $('#form').on('submit', submitForm);
};

// Improved extra client selector: shows full list on open and searches on term
function initExtraClientSelectorV2() {
  try { if ($('#extraClientId').data('select2')) { $('#extraClientId').select2('destroy'); } } catch {}
  $('#extraClientId').select2({
    theme: 'bootstrap-5',
    placeholder: 'Sélectionner un client extra',
    allowClear: true,
    minimumInputLength: 0,
    ajax: {
      transport: function (params, success, failure) {
        const term = (params.data && params.data.term) || '';
        if (!term) {
          $.ajax({ url: `${API_BASEURL}/extra-clients/fetch-all`, type: 'GET', success, error: failure });
        } else {
          $.ajax({ url: `${API_BASEURL}/extra-clients/list`, type: 'POST', data: { draw: 1, start: 0, length: 100, search: { value: term } }, success, error: failure });
        }
      },
      delay: 200,
      processResults: function (data) {
        const rows = Array.isArray(data) ? data : ((data && data.data) ? data.data : []);
        const results = rows.map(ec => ({ id: ec.id, text: `${ec.first_name || ''} ${ec.last_name || ''}${ec.company_name ? ' ('+ec.company_name+')' : ''} - ${ec.phone_number || ''}`.trim() }));
        return { results };
      }
    }
  });
}


function initExtraClientSelector() {
  $('#extraClientId').select2({
    theme: 'bootstrap-5',
    placeholder: 'Rechercher un client extra (nom, téléphone, société)',
    allowClear: true,
    ajax: {
      transport: function (params, success, failure) {
        $.ajax({
          url: `${API_BASEURL}/extra-clients/list`,
          type: 'POST',
          data: { draw: 1, start: 0, length: 10, search: { value: params.data.term || '' } },
          success,
          error: failure,
        });
      },
      delay: 250,
      processResults: function (data) {
        const rows = (data && data.data) ? data.data : [];
        const results = rows.map(ec => ({ id: ec.id, text: `${(ec.first_name||'')} ${(ec.last_name||'')}${ec.company_name ? ' ('+ec.company_name+')' : ''} - ${(ec.phone_number||'')}`.trim() }));
        return { results };
      }
    }
  });
}

