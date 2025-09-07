async function refresh() {
  $('#spinner').show();

  let { data } = await ajax({
    url: `${API_BASEURL}/partners/performances`,
    type: 'GET',
    data: {
      partner_id: $('#partnerId').val(),
      from_date: $('#fromDate').val(),
      to_date: $('#toDate').val(),
    },
  });

  $('#spinner').hide();

  let tbody = $('#table tbody');

  tbody.empty();

  let totals = {
    nb: 0,
    amount: 0,
    fee: 0,
    commission: 0,
    commission_platform: 0,
  };

  for (const opType of data) {
    if (![
      'account_recharge',
      'balance_withdrawal',
      'cards_sold',
      'decoders_sold',
      'invoices_paid',
      'invoices_unpaid',
    ].includes(opType.code)) {
      tbody.append(`
        <tr>
          <th><i class="${opType.icon_class}"></i> ${opType.name} ${
        opType.card_type ?? ''
      }</th>
          <td>${opType.nb}</td>
          <td>${formatAmountSpaced(opType.amount)}</td>
          <td>${formatAmountSpaced(opType.fee)}</td>
          <td>${formatAmountSpaced(opType.commission)}</td>
          <td>${formatAmountSpaced(opType.fee - opType.commission)}</td>
        </tr>
      `);

      totals.nb += parseInt(opType.nb);
      totals.amount += parseInt(opType.amount);
      totals.fee += parseInt(opType.fee);
      totals.commission += parseInt(opType.commission);
      totals.commission_platform += parseInt(opType.fee - opType.commission);
    }
  }

  tbody.append(`
    <tr class="text-danger">
      <th>Totaux</th>
      <th>${totals.nb}</th>
      <th>${formatAmountSpaced(totals.amount)}</th>
      <th>${formatAmountSpaced(totals.fee)}</th>
      <th>${formatAmountSpaced(totals.commission)}</th>
      <th>${formatAmountSpaced(totals.commission_platform)}</th>
    </tr>
  `);

  for (const opType of data) {
    if ([
      'account_recharge',
      'balance_withdrawal',
      'invoices_paid',
      'invoices_unpaid',
      'cards_sold',
      'decoders_sold',
    ].includes(opType.code)) {
      tbody.append(`
        <tr>
          <th><i class="${opType.icon_class}"></i> ${opType.name} ${
        opType.card_type ?? ''
      }</th>
          <td>${opType.nb}</td>
          <td>${formatAmountSpaced((
            opType.code === 'account_recharge'
              ? opType.data_trans_amount
              : opType.data_amount
          ))}</td>
          <td></td>
          <td></td>
        </tr>
      `);
    }
  }
}

window.render = function () {
  $('#partnerId, #fromDate, #toDate').change(function () {
    refresh();
  });

  $('#partnerId').change();

  populatePartners('#partnerId');
};
