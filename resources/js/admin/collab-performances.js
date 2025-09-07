async function loadData() {
  try {
    const payload = {
      from_date: $('#from_date').val() || '',
      to_date: $('#to_date').val() || '',
    };
    const { data } = await ajax({ url: `${API_BASEURL}/performance/collabs`, type: 'POST', data: payload });
    const tb = $('#tbl tbody');
    tb.html('');
    (data.data || []).forEach(row => {
      tb.append(`
        <tr>
          <td>${htmlEntities(row.name || '')}</td>
          <td>${htmlEntities(row.email || '')}</td>
          <td>${row.ops_count}</td>
          <td>${formatAmount(row.ops_amount)}</td>
        </tr>
      `);
    });
  } catch (e) {
    console.log(e);
    Toast.fire('Erreur de chargement', '', 'error');
  }
}

async function loadBreakdown() {
  try {
    const payload = {
      from_date: $('#from_date').val() || '',
      to_date: $('#to_date').val() || '',
    };
    const { data } = await ajax({ url: `${API_BASEURL}/performance/collabs-by-type`, type: 'POST', data: payload });
    const tb = $('#tblDetail tbody');
    tb.html('');
    let gCount = 0, gAmount = 0;
    // Grouper par collaborateur
    const grouped = {};
    (data.data || []).forEach(r => {
      if (!grouped[r.reviewer_id]) grouped[r.reviewer_id] = { info: r, rows: [] };
      grouped[r.reviewer_id].rows.push(r);
    });
    Object.values(grouped).forEach(g => {
      const name = g.info.reviewer_name || '';
      let subCount = 0, subAmount = 0;
      g.rows.forEach((r, idx) => {
        subCount += r.ops_count; subAmount += r.ops_amount;
        gCount += r.ops_count; gAmount += r.ops_amount;
        tb.append(`
          <tr>
            <td>${idx===0 ? htmlEntities(name) : ''}</td>
            <td>${htmlEntities(r.op_type_name || r.op_type_code || '')}</td>
            <td>${r.ops_count}</td>
            <td>${formatAmount(r.ops_amount)}</td>
          </tr>
        `);
      });
      // Sous-total
      tb.append(`
        <tr>
          <td colspan="2" class="text-end"><strong>Sous-total ${htmlEntities(name)}</strong></td>
          <td><strong>${subCount}</strong></td>
          <td><strong>${formatAmount(subAmount)}</strong></td>
        </tr>
      `);
    });
    $('#detailTotalCount').text(gCount);
    $('#detailTotalAmount').text(formatAmount(gAmount));
  } catch (e) {
    console.log(e);
    Toast.fire('Erreur de chargement (détails)', '', 'error');
  }
}

window.render = async function () {
  $('#btnFilter').on('click', loadData);
  $('#btnFilter').on('click', loadBreakdown);
  await loadData();
  await loadBreakdown();
};
