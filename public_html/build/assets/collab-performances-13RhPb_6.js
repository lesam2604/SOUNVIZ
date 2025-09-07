async function m(){try{const a={from_date:$("#from_date").val()||"",to_date:$("#to_date").val()||""},{data:r}=await ajax({url:`${API_BASEURL}/performance/collabs`,type:"POST",data:a}),n=$("#tbl tbody");n.html(""),(r.data||[]).forEach(e=>{n.append(`
        <tr>
          <td>${htmlEntities(e.name||"")}</td>
          <td>${htmlEntities(e.email||"")}</td>
          <td>${e.ops_count}</td>
          <td>${formatAmount(e.ops_amount)}</td>
        </tr>
      `)})}catch(a){console.log(a),Toast.fire("Erreur de chargement","","error")}}async function u(){try{const a={from_date:$("#from_date").val()||"",to_date:$("#to_date").val()||""},{data:r}=await ajax({url:`${API_BASEURL}/performance/collabs-by-type`,type:"POST",data:a}),n=$("#tblDetail tbody");n.html("");let e=0,s=0;const d={};(r.data||[]).forEach(t=>{d[t.reviewer_id]||(d[t.reviewer_id]={info:t,rows:[]}),d[t.reviewer_id].rows.push(t)}),Object.values(d).forEach(t=>{const l=t.info.reviewer_name||"";let c=0,i=0;t.rows.forEach((o,p)=>{c+=o.ops_count,i+=o.ops_amount,e+=o.ops_count,s+=o.ops_amount,n.append(`
          <tr>
            <td>${p===0?htmlEntities(l):""}</td>
            <td>${htmlEntities(o.op_type_name||o.op_type_code||"")}</td>
            <td>${o.ops_count}</td>
            <td>${formatAmount(o.ops_amount)}</td>
          </tr>
        `)}),n.append(`
        <tr>
          <td colspan="2" class="text-end"><strong>Sous-total ${htmlEntities(l)}</strong></td>
          <td><strong>${c}</strong></td>
          <td><strong>${formatAmount(i)}</strong></td>
        </tr>
      `)}),$("#detailTotalCount").text(e),$("#detailTotalAmount").text(formatAmount(s))}catch(a){console.log(a),Toast.fire("Erreur de chargement (détails)","","error")}}window.render=async function(){$("#btnFilter").on("click",m),$("#btnFilter").on("click",u),await m(),await u()};
