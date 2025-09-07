async function n(){$("#spinner").show();let{data:o}=await ajax({url:`${API_BASEURL}/partners/performances`,type:"GET",data:{partner_id:$("#partnerId").val(),from_date:$("#fromDate").val(),to_date:$("#toDate").val()}});$("#spinner").hide();let e=$("#table tbody");e.empty();let a={nb:0,amount:0,fee:0,commission:0,commission_platform:0};for(const t of o)["account_recharge","balance_withdrawal","cards_sold","decoders_sold","invoices_paid","invoices_unpaid"].includes(t.code)||(e.append(`
        <tr>
          <th><i class="${t.icon_class}"></i> ${t.name} ${t.card_type??""}</th>
          <td>${t.nb}</td>
          <td>${formatAmountSpaced(t.amount)}</td>
          <td>${formatAmountSpaced(t.fee)}</td>
          <td>${formatAmountSpaced(t.commission)}</td>
          <td>${formatAmountSpaced(t.fee-t.commission)}</td>
        </tr>
      `),a.nb+=parseInt(t.nb),a.amount+=parseInt(t.amount),a.fee+=parseInt(t.fee),a.commission+=parseInt(t.commission),a.commission_platform+=parseInt(t.fee-t.commission));e.append(`
    <tr class="text-danger">
      <th>Totaux</th>
      <th>${a.nb}</th>
      <th>${formatAmountSpaced(a.amount)}</th>
      <th>${formatAmountSpaced(a.fee)}</th>
      <th>${formatAmountSpaced(a.commission)}</th>
      <th>${formatAmountSpaced(a.commission_platform)}</th>
    </tr>
  `);for(const t of o)["account_recharge","balance_withdrawal","invoices_paid","invoices_unpaid","cards_sold","decoders_sold"].includes(t.code)&&e.append(`
        <tr>
          <th><i class="${t.icon_class}"></i> ${t.name} ${t.card_type??""}</th>
          <td>${t.nb}</td>
          <td>${formatAmountSpaced(t.code==="account_recharge"?t.data_trans_amount:t.data_amount)}</td>
          <td></td>
          <td></td>
        </tr>
      `)}window.render=function(){$("#partnerId, #fromDate, #toDate").change(function(){n()}),$("#partnerId").change(),populatePartners("#partnerId")};
