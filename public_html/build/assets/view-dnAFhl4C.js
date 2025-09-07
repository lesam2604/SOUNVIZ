let t=null;async function g(s){const{data:n}=await ajax({url:`${API_BASEURL}/invoices/fetch/${s}`,type:"GET"});return n}async function b(){var s;try{const{data:n}=await ajax({url:`${API_BASEURL}/invoices/mark-paid/${t.id}`,type:"POST"});Toast.fire(n.message||"Facture validée","","success"),location.reload()}catch(n){console.log(n),Toast.fire(((s=n==null?void 0:n.responseJSON)==null?void 0:s.message)||"Erreur","","error")}}function h(){var a,i,r,o,c,d,l,u,v;$("#invCode").text(t.code);const s=t.client_type==="partner"?`${((i=(a=t.partner)==null?void 0:a.user)==null?void 0:i.first_name)||""} ${((o=(r=t.partner)==null?void 0:r.user)==null?void 0:o.last_name)||""}`:t.client_name||"",n=t.issuer?`${t.issuer.first_name||""} ${t.issuer.last_name||""}`:"",p=(t.items||[]).map(e=>`
    <tr><td>${htmlEntities(e.label)}</td><td>${e.qty}</td><td>${formatAmount(e.unit)}</td><td>${formatAmount((e.qty||0)*(e.unit||0))}</td></tr>
  `).join(""),m=t.status==="unpaid"?'<button id="btnMarkPaid" class="btn btn-success"><i class="fas fa-check"></i> Marquer comme payé</button>':"";$("#content").html(`
    <div class="row mb-3">
      <div class="col-12 col-lg-6">
        <div><strong>Entreprise:</strong> AHOTANTI</div>
        <div><strong>Type d'opération:</strong> ${((c=t.operation_type)==null?void 0:c.name)||""}</div>
        <div><strong>Client:</strong> ${s}</div>
        <div><strong>Téléphone:</strong> ${t.client_phone||((l=(d=t.partner)==null?void 0:d.user)==null?void 0:l.phone_number)||""}</div>
        <div><strong>Email:</strong> ${t.client_email||((v=(u=t.partner)==null?void 0:u.user)==null?void 0:v.email)||""}</div>
        <div><strong>Émis par:</strong> ${n}</div>
      </div>
      <div class="col-12 col-lg-6 text-lg-end">
        <div><strong>Statut:</strong> <span class="badge ${t.status==="paid"?"bg-success":"bg-warning text-dark"}">${t.status}</span></div>
        <div><strong>Montant:</strong> ${formatAmount(t.total_amount)} ${t.currency}</div>
        <div class="mt-2">${m}</div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Désignation</th><th>Qté</th><th>PU</th><th>Total</th></tr></thead>
        <tbody>${p||'<tr><td colspan="4" class="text-center">Aucune ligne</td></tr>'}</tbody>
      </table>
    </div>
  `),$("#btnMarkPaid").on("click",b),$("#btnPrint").on("click",()=>window.print()),$("#btnCsv").attr("href",`/invoices/export-csv/${t.id}`),$("#btnPdf").attr("href",`/invoices/export-pdf/${t.id}`)}window.render=async function(){const s=location.pathname.split("/"),n=s[s.length-1];t=await g(n),h()};
