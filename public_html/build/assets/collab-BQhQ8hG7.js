function r(){let a=[["Opérations en attente","pending","blue"],["Opérations validées","approved","green"],["Opérations rejetées","rejected","red"]];for(const[e,n,o]of a){let t=$(`<div class="row"><h5>${e}</h5></div>`);for(const s of SETTINGS.opTypes)t.append(`
        <div class="col-12 col-lg-4 col-md-6">
          <div class="card">
            <div class="card-body px-4 py-4">
              <div class="row">
                <div class="col-3 d-flex justify-content-start">
                  <div class="stats-icon ${o} mb-2">
                    <i class="${s.icon_class}"></i>
                  </div>
                </div>
                <div class="col-9">
                  <h6 class="text-muted font-semibold">${s.name}</h6>
                  <h6 class="font-extrabold mb-0" id="${s.code}_${n}">0</h6>
                  <div class="text-end">
                    <a href="/operations/${s.code}/${n}" title="Acceder a la liste"
                      class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> 
      `);t.insertBefore("#otherOps")}}function i(a){let e={pending:0,approved:0,rejected:0};for(const t of SETTINGS.opTypes)for(const s of["pending","approved","rejected"])e[s]+=a[`${t.code}_${s}`];let n={series:[e.pending,e.approved,e.rejected],labels:["En attente","Validées","Rejetées"],colors:["#57caeb","#5ddab4","#ff7976"],chart:{type:"donut",width:"100%",height:"350px"},legend:{position:"bottom"},plotOptions:{pie:{donut:{size:"30%"}}}};new ApexCharts(document.getElementById("chartOperationStatus"),n).render()}async function c(){let{data:a}=await ajax({url:`${API_BASEURL}/collabs/dashboard-data`,type:"GET"});const e=new Intl.NumberFormat("fr-FR");for(const t in a)["to_supply_products","histories","recent_partners"].includes(t)===!1&&$("#"+t).text(e.format(a[t]));let n=$("#tableActivities tbody").empty();for(const t of a.histories)n.append(`
      <tr title="${t.content}">
        <td class="col-12">
          <div class="d-flex align-items-center">
            <div class="avatar avatar-md">
              <img src="${getThumbnailUrl(USER.picture)}">
            </div>
            <p class="font-bold ms-3 mb-0">${t.title}</p>
          </div>
        </td>
      </tr>
    `);let o=$("#recentPartners").empty();for(const t of a.recent_partners)o.append(`
      <div class="recent-message d-flex px-4 py-3">
        <div class="avatar avatar-lg">
          <img src="${getThumbnailUrl(t.user.picture)}">
        </div>
        <div class="name ms-4">
          <h5 class="mb-1">${t.user.first_name+" "+t.user.last_name}</h5>
          <h6 class="text-muted mb-0">${t.user.email}</h6>
        </div>
      </div>
    `);i(a)}function l(){try{if(!(USER.hasRole("admin")||USER.hasRole("collab"))){$("#newOperationBlock").hide();return}const e=$("#newOpType");if(!e.length)return;e.empty().append('<option value="">Sélectionner un type...</option>'),(SETTINGS.opTypes||[]).forEach(n=>{e.append(`<option value="${n.code}">${n.name}</option>`)}),e.on("change",function(){const n=$(this).val();n?$("#newOpBtn").removeClass("disabled").attr("href",`/operations/${n}/create`):$("#newOpBtn").addClass("disabled").attr("href","#")})}catch(a){console.warn("initNewOperationBlock",a)}}window.render=async function(){r(),await c(),$("#dashboardMessage").html(SETTINGS.dashboardMessage),l()};(async function(){try{const e=await $.get(`${API_BASEURL}/collabs/me/balance`);if(e!=null&&e.ok){const n=Number(e.balance||0);$("#myBalanceAmount").text(n.toLocaleString("fr-FR")+" FCFA"),$("#myBalanceCurrency").text("")}else console.warn("Balance API response not ok:",e)}catch(e){console.error("Erreur loadMyBalance:",e)}})();if(typeof window.render=="function"){const a=window.render;window.render=async function(){try{await a()}catch{}refreshMyBalance()}}else $(refreshMyBalance);
