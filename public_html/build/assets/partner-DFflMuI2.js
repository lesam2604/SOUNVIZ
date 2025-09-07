function d(){let t=[["Opérations en attente","pending","blue"],["Opérations validées","approved","green"],["Opérations rejetées","rejected","red"]];for(const[a,o,e]of t){let r=$(`<div class="row"><h5>${a}</h5></div>`);for(const s of SETTINGS.opTypes)r.append(`
        <div class="col-12 col-lg-4 col-md-6">
          <div class="card">
            <div class="card-body px-4 py-4">
              <div class="row">
                <div class="col-3 d-flex justify-content-start">
                  <div class="stats-icon ${e} mb-2">
                    <i class="${s.icon_class}"></i>
                  </div>
                </div>
                <div class="col-9">
                  <h6 class="text-muted font-semibold">${s.name}</h6>
                  <h6 class="font-extrabold mb-0" id="${s.code}_${o}">0</h6>
                  <div class="text-end">
                    <a href="/operations/${s.code}/${o}" title="Acceder a la liste"
                      class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> 
      `);r.insertBefore("#otherOps")}}function i(t){let a={pending:0,approved:0,rejected:0};for(const r of SETTINGS.opTypes)for(const s of["pending","approved","rejected"])a[s]+=t[`${r.code}_${s}`];let o={series:[a.pending,a.approved,a.rejected],labels:["En attente","Validées","Rejetées"],colors:["#57caeb","#5ddab4","#ff7976"],chart:{type:"donut",width:"100%",height:"350px"},legend:{position:"bottom"},plotOptions:{pie:{donut:{size:"30%"}}}};new ApexCharts(document.getElementById("chartOperationStatus"),o).render()}async function n(){let{data:t}=await ajax({url:`${API_BASEURL}/partners/dashboard-data`,type:"GET"});const a=new Intl.NumberFormat("fr-FR");for(const e in t)e!=="histories"&&$("#"+e).text(a.format(t[e]));let o=$("#tableActivities tbody").empty();for(const e of t.histories)o.append(`
       <tr title="${e.content}">
         <td class="col-12">
           <div class="d-flex align-items-center">
             <div class="avatar avatar-md">
               <img src="${getThumbnailUrl(USER.picture)}">
             </div>
             <p class="font-bold ms-3 mb-0">${e.title}</p>
           </div>
         </td>
       </tr>
     `);i(t)}async function c(){let{data:t}=await ajax({url:`${API_BASEURL}/cards/total-stock`,type:"POST",data:{}});$("#allCards").text(t.total),$("#activatedCards").text(t.activated??0),$("#notActivatedCards").text(t.not_activated??0)}async function l(){let{data:t}=await ajax({url:`${API_BASEURL}/decoders/total-stock`,type:"POST",data:{}});$("#allDecoders").text(t.total),$("#activatedDecoders").text(t.activated??0),$("#notActivatedDecoders").text(t.not_activated??0)}window.render=async function(){d(),await Promise.all([n(),USER.hasRole("partner-master")?c():Promise.resolve(),USER.hasRole("partner-master")?l():Promise.resolve()]),$("#dashboardMessage").html(SETTINGS.dashboardMessage)};
