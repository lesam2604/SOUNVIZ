async function l(){try{const{data:a}=await ajax({url:`${API_BASEURL}/invoices/list`,type:"POST",data:{status:$("#status").val()||""}}),e=$("#tbl tbody");e.html("");const u=(a.data||[]).map(t=>{var s,r,n,o,d,c;const i=t.client_type==="partner"?((r=(s=t.partner)==null?void 0:s.user)==null?void 0:r.first_name)+" "+((o=(n=t.partner)==null?void 0:n.user)==null?void 0:o.last_name)+" ("+((d=t.partner)==null?void 0:d.code)+")":t.client_name;return`
        <tr>
          <td>${t.code}</td>
          <td>${((c=t.operation_type)==null?void 0:c.name)||""}</td>
          <td>${i||""}</td>
          <td>${formatAmount(t.total_amount)}</td>
          <td><span class="badge ${t.status==="paid"?"bg-success":"bg-warning text-dark"}">${t.status}</span></td>
          <td>${formatDateTime(t.created_at)}</td>
          <td><a class="btn btn-sm btn-outline-primary" href="/invoices/${t.id}"><i class="fas fa-eye"></i></a></td>
        </tr>`});e.html(u.join(""))}catch(a){console.log(a),Toast.fire("Erreur de chargement","","error")}}window.render=async function(){$("#status").on("change",l),await l()};
