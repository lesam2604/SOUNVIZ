async function loadInvoices() {
  try {
    const { data } = await ajax({
      url: `${API_BASEURL}/invoices/list`,
      type: 'POST',
      data: { status: $('#status').val() || '' },
    });
    const tb = $('#tbl tbody');
    tb.html('');
    const rows = (data.data || []).map(inv => {
      const client = inv.client_type === 'partner'
        ? (inv.partner?.user?.first_name + ' ' + inv.partner?.user?.last_name + ' (' + inv.partner?.code + ')')
        : inv.client_name;
      return `
        <tr>
          <td>${inv.code}</td>
          <td>${inv.operation_type?.name || ''}</td>
          <td>${client || ''}</td>
          <td>${formatAmount(inv.total_amount)}</td>
          <td><span class="badge ${inv.status==='paid'?'bg-success':'bg-warning text-dark'}">${inv.status}</span></td>
          <td>${formatDateTime(inv.created_at)}</td>
          <td><a class="btn btn-sm btn-outline-primary" href="/invoices/${inv.id}"><i class="fas fa-eye"></i></a></td>
        </tr>`;
    });
    tb.html(rows.join(''));
  } catch (e) {
    console.log(e);
    Toast.fire('Erreur de chargement', '', 'error');
  }
}

window.render = async function () {
  $('#status').on('change', loadInvoices);
  await loadInvoices();
};

