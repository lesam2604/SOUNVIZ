function d(){let a=0;const t=$("#tabOthers").closest("li"),n=$("#paneOthers");for(const s of SETTINGS.opTypes)if(!["account_recharge","balance_withdrawal"].includes(s.code))for(const e in s.fees){t.before(`
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="tab-${s.code}-${e}"
            data-bs-toggle="tab" role="tab"
            href="#pane-${s.code}-${e}"
            aria-controls="pane-${s.code}-${e}"
            aria-selected="false">
            ${s.name}${e?` (${e})`:""}
          </a>
        </li>
      `);let o=$(`
        <div class="tab-pane fade" role="tabpanel"
          id="pane-${s.code}-${e}"
          aria-labelledby="tab-${s.code}-${e}">
          <div class="row">
            <h4 class="text-center">
              ${s.name}${e?` (${e})`:""}
            </h4>
            <hr>
          </div>
        </div>
      `).insertBefore(n).find(".row");[["fees","Frais de courses"],["commissions","Commissions du partenaire"]].forEach(([i,l])=>{const c=o.append(`<div class="col-12 col-lg-6">
            <div class="${i}-container">
              <h5>${l}</h5>
              <hr>
            </div>
          </div>`).find(`.${i}-container`);s[i][e].forEach(r=>{c.append(`
            <div class="step row mb-3 ms-3 pb-3 border-bottom align-items-end">
              <div class="col-12 col-lg-5 mb-3">
                <label for="${++a}Breakpoint" class="form-label">Seuil</label>
                <input type="number" class="form-control breakpoint" id="${a}Breakpoint"
                  placeholder="Infinie" value="${r.breakpoint}">
              </div>
              <div class="col-12 col-lg-5 mb-3">
                <label for="${a}Value" class="form-label">Valeur</label>
                <input type="text" class="form-control value" id="${a}Value" required"
                  placeholder="Valeur à appliquer" value="${r.value}">
              </div>
              <div class="col-12 col-lg-2 mb-3">
                <button type="button" class="btn btn-primary step-add"><i class="fas fa-plus"></i></button>
                <button type="button" class="btn btn-danger step-remove"><i class="fas fa-minus"></i></button>
              </div>
            </div>
          `)})}),$(`
        <div class="text-center">
          <button type="button" class="btn btn-primary btn-lg save-settings"><i class="fas fa-save"></i>
            Sauvegarder</button>
        </div>
      `).appendTo(o).find(".save-settings").data({opType:s,cardType:e})}$("#dashboardMessage").val(SETTINGS.dashboardMessage),$("#myTab .nav-link").first().tab("show")}async function p(){swalLoading();try{let{data:a}=await ajax({url:`${API_BASEURL}/settings/update-dashboard-message`,type:"POST",data:{dashboard_message:$("#dashboardMessage").val()}});Toast.fire(a.message,"","success")}catch({error:a}){Swal.fire(a.responseJSON.message,"","error")}}async function b(a,t){const n={},s=$(`#pane-${a.code}-${t}`);["fees","commissions"].forEach(e=>{n[e]=[],s.find(`.${e}-container .step`).each(function(){const o=$(this);n[e].push({breakpoint:o.find(".breakpoint").val(),value:o.find(".value").val()})})}),swalLoading();try{let{data:e}=await ajax({url:`${API_BASEURL}/settings`,type:"POST",data:{operation_type_id:a.id,card_type:t,op_type_data:JSON.stringify(n)}});Toast.fire(e.message,"","success")}catch({error:e}){console.log(e),Swal.fire(e.responseJSON.message,"","error")}}window.render=function(){d(),$("#updateDashboardMessage").click(function(a){p()}),$("#tabPanes").on("click",".step-add",function(){const a=$(this).closest(".step"),t=a.clone();t.find(".breakpoint").val(""),t.find(".value").val(""),t.insertAfter(a)}).on("click",".step-remove",function(){const a=$(this).closest(".step");if(!a.siblings(".step").length){Toast.fire("Impossible de supprimer la seule valeur disponible","","error");return}a.remove()}).on("click",".save-settings",function(){b($(this).data("opType"),$(this).data("cardType"))})};
