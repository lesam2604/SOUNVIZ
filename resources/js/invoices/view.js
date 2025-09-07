let invoice = null;

async function fetchInvoice(id) {
  const { data } = await ajax({ url: `${API_BASEURL}/invoices/fetch/${id}`, type: 'GET' });
  return data;
}

async function markPaid() {
  try {
    const { data } = await ajax({ url: `${API_BASEURL}/invoices/mark-paid/${invoice.id}`, type: 'POST' });
    Toast.fire(data.message || 'Facture validée', '', 'success');
    location.reload();
  } catch (e) {
    console.log(e);
    Toast.fire(e?.responseJSON?.message || 'Erreur', '', 'error');
  }
}

function renderContent() {
  $('#invCode').text(invoice.code);
  const client = invoice.client_type === 'partner'
    ? `${invoice.partner?.user?.first_name || ''} ${invoice.partner?.user?.last_name || ''}`
    : (invoice.client_name || '');
  const issuer = invoice.issuer ? `${invoice.issuer.first_name || ''} ${invoice.issuer.last_name || ''}` : '';
  const itemsRows = (invoice.items || []).map(it => `
    <tr><td>${htmlEntities(it.label)}</td><td>${it.qty}</td><td>${formatAmount(it.unit)}</td><td>${formatAmount((it.qty||0)*(it.unit||0))}</td></tr>
  `).join('');
  const actions = invoice.status === 'unpaid'
    ? `<button id="btnMarkPaid" class="btn btn-success"><i class="fas fa-check"></i> Marquer comme payé</button>`
    : '';

  $('#content').html(`
    <div class="row mb-3">
      <div class="col-12 col-lg-6">
        <div><strong>Entreprise:</strong> AHOTANTI</div>
        <div><strong>Type d'opération:</strong> ${invoice.operation_type?.name || ''}</div>
        <div><strong>Client:</strong> ${client}</div>
        <div><strong>Téléphone:</strong> ${invoice.client_phone || (invoice.partner?.user?.phone_number || '')}</div>
        <div><strong>Email:</strong> ${invoice.client_email || (invoice.partner?.user?.email || '')}</div>
        <div><strong>Émis par:</strong> ${issuer}</div>
      </div>
      <div class="col-12 col-lg-6 text-lg-end">
        <div><strong>Statut:</strong> <span class="badge ${invoice.status==='paid'?'bg-success':'bg-warning text-dark'}">${invoice.status}</span></div>
        <div><strong>Montant:</strong> ${formatAmount(invoice.total_amount)} ${invoice.currency}</div>
        <div class="mt-2">${actions}</div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Désignation</th><th>Qté</th><th>PU</th><th>Total</th></tr></thead>
        <tbody>${itemsRows || '<tr><td colspan="4" class="text-center">Aucune ligne</td></tr>'}</tbody>
      </table>
    </div>
  `);

  $('#btnMarkPaid').on('click', markPaid);
  // Export + Print buttons
  $('#btnPrint').on('click', () => window.print());
  // Utiliser les routes web (pour éviter les problèmes d'auth token en nouvelle fenêtre)
  $('#btnCsv').attr('href', `/invoices/export-csv/${invoice.id}`);
  $('#btnPdf').attr('href', `/invoices/export-pdf/${invoice.id}`);
}

window.render = async function () {
  const parts = location.pathname.split('/');
  const id = parts[parts.length - 1];
  invoice = await fetchInvoice(id);
  renderContent();
};
