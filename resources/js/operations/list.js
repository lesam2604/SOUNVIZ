/* resources/js/operations/list.js */

let opType = null;

// --- helpers ---
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}
function apiBase() {
  return (typeof API_BASEURL !== 'undefined' && API_BASEURL) ? API_BASEURL : '';
}
function fetchOpType() {
  const opTypeCode = $('#opTypeCode').val();
  opType = SETTINGS.opTypes.find((o) => o.code === opTypeCode);
}

// --- Export PDF carte ---
async function exportCardPdf(selectedObject) {
  swalLoading();
  try {
    await downloadFile(
      `${apiBase()}/operations/card-activation-export-pdf`,
      'GET',
      { operation_id: selectedObject.id }
    );
    Swal.close();
  } catch (e) {
    console.error(e);
    const msg = e?.error?.responseJSON?.message || 'Erreur export PDF';
    Swal.fire(msg, '', 'error');
  }
}

// --- (ré)initialisation DataTable propre ---
function initDataTable() {
  console.log('[list.js] initDataTable()');

  // Si déjà initialisée, on détruit proprement et on reconstruit l’ossature
  if ($.fn.DataTable.isDataTable('#table')) {
    try {
      $('#table').DataTable().clear().destroy(true);
    } catch (e) {
      console.warn('[list.js] destroy warning:', e);
    }
  }
  // Reconstruire la structure basique du tableau
  const $table = $('#table');
  $table.off(); // enlève d’éventuels handlers déjà accrochés au tableau
  $table.html('<thead></thead><tbody></tbody>');

  const $thead = $('#table thead');
  const $tr = $(`
    <tr>
      <th>#</th>
      <th>Code</th>
      <th>Statut</th>
    </tr>
  `).appendTo($thead);

  const columns = [
    { data: '__no__', name: 'created_at' },
    {
      data: 'code',
      name: 'code',
      render: (data, type, row) => `<a href="/operations/${opType.code}/${row.id}">${data}</a>`,
    },
    {
      data: 'status',
      name: 'status',
      render: (data) => {
        const s = String(data ?? '');
        if (s.includes('<')) return s; // déjà un badge HTML
        const low = s.toLowerCase();
        if (s === 'pending' || low === 'en attente')
          return '<span class="badge bg-secondary">En attente</span>';
        if (s === 'approved' || low === 'validée' || low === 'validee')
          return '<span class="badge bg-success">Validée</span>';
        if (s === 'rejected' || low === 'rejetée' || low === 'rejetee')
          return '<span class="badge bg-danger">Rejetée</span>';
        return s;
      },
    },
  ];

  // Colonnes dynamiques selon opType
  for (const [fieldName, fieldData] of opType.sorted_fields) {
    if (!fieldData.listed) continue;

    if (opType.code === 'account_recharge' && fieldName === 'trans_amount') {
      $tr.append(`<th>Montant avant prélèvement</th><th>Frais (MomoPay)</th>`);
      columns.push(
        {
          data: (row) =>
            row.data.sender_phone_number_type === 'MomoPay'
              ? row.data.trans_amount / (1 - 0.005)
              : row.data.trans_amount,
          searchable: false,
          orderable: false,
          render: (val) => formatAmount(val),
        },
        {
          data: (row) =>
            row.data.sender_phone_number_type === 'MomoPay'
              ? (row.data.trans_amount * 0.005) / (1 - 0.005)
              : 0,
          searchable: false,
          orderable: false,
          render: (val) => formatAmount(val),
        }
      );
    }

    $tr.append(`<th>${fieldName === opType.amount_field ? 'Montant' : fieldData.label}</th>`);
    columns.push({
      data: (row) => row.data?.[fieldName] ?? '',
      searchable: false,
      orderable: false,
      render: (val) => (fieldData.is_amount ? formatAmount(val) : val),
    });
  }

  // Opérateur & date
  $tr.append(`<th>Opérateur</th><th>Effectuée le</th>`);
  columns.push(
    {
      data: 'partner',
      name: 'partner',
      render: (data, type, row) => {
        return USER.partner && USER.partner.id === row.partner_id
          ? `${data} (${row.partner_code})`
          : `${data} (<a href="/partners/${row.partner_id}">${row.partner_code}</a>)`;
      },
    },
    { data: 'created_at', name: 'created_at' }
  );

  

  // Création de la DataTable + handlers
  window.datatable = $('#table')
    .on('click', '.export-card', function () {
      const row = window.datatable.row($(this).closest('tr')).data();
      exportCardPdf(row);
    })
    .on('click', '.js-cancel-request', async function () {
      const id = $(this).data('id');
      const confirm = await Swal.fire({
        title: 'Confirmer la demande ?',
        text: "Cette demande sera envoyée à l'administrateur.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, envoyer',
        cancelButtonText: 'Annuler',
      });
      if (!confirm.isConfirmed) return;

      try {
        swalLoading('Envoi de la demande...');
        // IMPORTANT : route web (même origine) + CSRF -> pas d’erreur CSRF
        const headers = { 'X-CSRF-TOKEN': getCsrfToken() };
        const res = await axios.post(`/collab/operations-cancel`, { operation_id: id }, { headers });
        Swal.fire(res?.data?.message || 'Demande envoyée.', '', 'success');
      } catch (e) {
        console.error(e);
        const msg = e?.response?.data?.message || 'Erreur pendant l’envoi.';
        Swal.fire(msg, '', 'error');
      }
    })
    .DataTable({
      processing: true,
      serverSide: true,
      columns,
      order: [0, 'desc'],
      ajax: {
        url: `${apiBase()}/operations/${opType.code}/list`,
        type: 'POST',
        dataType: 'json',
        data: (d) => {
          d.operation_type_id = opType.id;
          d.partner_id = $('#partnerId').val();
          d.from_date = $('#fromDate').val();
          d.to_date = $('#toDate').val();
          d.status = $('#status').val();
          d.card_type = $('#cardType').val() ?? '';
          d.uba_type = $('#ubaType').val() ?? '';

          // DataTables envoie d.columns, mais selon versions/extensions cela peut manquer
          if (!Array.isArray(d.columns)) d.columns = [];

          if (['card_activation', 'card_recharge', 'card_deactivation'].includes(opType.code)) {
            d.columns.push({ data: 'card_id', name: 'card_id', searchable: true, orderable: false });
          }
          if (['canal_activation', 'canal_resub'].includes(opType.code)) {
            d.columns.push({ data: 'decoder_number', name: 'decoder_number', searchable: true, orderable: false });
          }
        },
        error: (error) => {
          const status = error?.status || error?.xhr?.status;
          const body = error?.responseJSON?.message || error?.responseText || 'Erreur chargement liste';
          console.error('[list.js] ajax error', { status, error });
          Swal.fire(`${body}${status ? ` (HTTP ${status})` : ''}`, '', 'error');
        },
        dataSrc: (response) => {
          const rows = (response?.data || []).map((row) => {
            try { row.data = JSON.parse(row.data); } catch {}
            const raw = String(row.status ?? '');
            const plain = raw.replace(/<[^>]+>/g, '').trim(); // enlève les éventuels badges HTML
            row.__status_plain = plain.toLowerCase();
            return row;
          });
          console.log('[list.js] dataSrc rows:', rows.length);
          return rows;
        },
      },
      pageLength: 25,
      autoWidth: false,
      deferLoading: 0,
    });

  $('#table').wrap('<div style="overflow-x: auto;"></div>');
  console.log('[list.js] DataTable ready -> typeof window.datatable =', typeof window.datatable);
}

// --- autres inits ---
async function otherInits() {
  setTitle(`Liste des opérations «${opType.name}»`);

  if (USER.hasRole('partner-pos') && ['balance_withdrawal'].includes(opType.code)) {
    $('#linkCreate').hide();
  } else {
    $('#linkCreate')
      .html(`<i class="fas fa-plus"></i> Ajouter une nouvelle opération`)
      .attr('href', `/operations/${opType.code}/create`);
  }

  $('#exportExcel').click(async function (e) {
    e.preventDefault();
    swalLoading();
    await downloadFile(
      `${apiBase()}/operations/${opType.code}/export-excel`,
      'GET',
      {
        partner_id: $('#partnerId').val() ?? '',
        from_date: $('#fromDate').val(),
        to_date: $('#toDate').val(),
        status: $('#status').val(),
        card_type: $('#cardType').val(),
        uba_type: $('#ubaType').val(),
      }
    );
    Swal.close();
  });

  $('#exportPdf').click(async function (e) {
    e.preventDefault();
    swalLoading();
    await downloadFile(
      `${apiBase()}/operations/${opType.code}/export-pdf`,
      'GET',
      {
        partner_id: $('#partnerId').val() ?? '',
        from_date: $('#fromDate').val(),
        to_date: $('#toDate').val(),
        status: $('#status').val(),
        card_type: $('#cardType').val(),
        uba_type: $('#ubaType').val(),
      }
    );
    Swal.close();
  });

  $('#status, #partnerId, #fromDate, #toDate, #cardType, #ubaType').change(
    function () {
      if (window.datatable) window.datatable.draw();
    }
  );

  $('#status').val($('#opStatus').val()).change();

  if (USER.hasRole('partner')) {
    $('#partnerId').parent().removeClass('d-flex').addClass('d-none');
  } else {
    populatePartners('#partnerId');
  }

  if (!['card_activation', 'card_recharge', 'card_deactivation'].includes(opType.code)) {
    $('#cardType').parent().removeClass('d-flex').addClass('d-none');
  } else {
    populateCardTypes('#cardType');
  }

  if (USER.hasRole('partner') || opType.code !== 'card_activation') {
    $('#ubaType').parent().removeClass('d-flex').addClass('d-none');
  } else {
    populateUbaTypes('#ubaType');
  }
}

// --- point d’entrée page ---
window.render = async function () {
  console.log('[list.js] render()');
  fetchOpType();
  initDataTable();
  await otherInits();
};

// --- AUTO-INIT ---
(function autoBoot() {
  console.log('[list.js] loaded, autoBoot()');
  if (document.readyState !== 'loading') {
    window.render();
  } else {
    document.addEventListener('DOMContentLoaded', () => window.render(), { once: true });
  }
})();
