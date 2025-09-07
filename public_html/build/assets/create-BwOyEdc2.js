let r=null,i=null,m=0;function g(){let a=$("#opTypeCode").val();i=SETTINGS.opTypes.find(l=>l.code===a)}async function x(){let a=$("#objectId").val();if(a)try{let{data:l}=await ajax({url:`${API_BASEURL}/operations/${i.code}/fetch/${a}`,type:"GET"});r=l}catch({error:l}){await Swal.fire(l.responseJSON.message,"","error"),location=`/operations/${i.code}`}}function y(){for(const[a,l]of i.sorted_fields)if(l.stored)if(a===i.amount_field)$(`#${a}`).val("0").keyup();else switch(l.type){case"select":case"text":case"textarea":case"email":case"file":case"date":case"datetime":$(`#${a}`).val("");break;case"number":$(`#${a}`).val("0");break;case"card":$(`#${a}`).val(""),$(`#${a}_digits`).val("10").change();break;case"country":$(`#${a}`).val(24).change();break}$(".update-file-helper").hide(),$(".is-invalid").removeClass("is-invalid")}function k(){for(const[a,l]of i.sorted_fields)if(l.updated)if(i.code==="account_recharge"&&a==="trans_amount"&&r.data.sender_phone_number_type==="MomoPay")$(`#${a}`).val(r.data[a]/(1-.005));else if(a===i.amount_field)$(`#${a}`).val(r.data[a]).keyup();else switch(l.type){case"select":case"text":case"textarea":case"email":case"date":case"datetime":case"number":$(`#${a}`).val(r.data[a]);break;case"card":$(`#${a}`).val(r.data[a]),$(`#${a}_digits`).val(r.data[a].length).change();break;case"country":$(`#${a}`).val(r.data[a]).change();break;case"file":$(`#${a}`).val("");break}}function w(){try{$("#partnerSelectBlock").show(),$("#partnerId").data("select2")||$("#partnerId").select2({theme:"bootstrap-5",placeholder:"Rechercher un partenaire (code, nom, société)",allowClear:!0,ajax:{transport:function(a,l,t){$.ajax({url:`${API_BASEURL}/partners/fetch-by-term`,type:"GET",data:{term:a.data.term||""},success:l,error:t})},delay:250,processResults:function(a){return{results:(a||[]).map(t=>({id:t.id,text:`${t.code} - ${t.first_name} ${t.last_name}${t.company_name?" ("+t.company_name+")":""}`}))}}}})}catch(a){console.warn("initPartnerSelector error:",a)}}function S(){const a=$("#blockSubmit");for(const[l,t]of i.sorted_fields)if(t.created||t.updated){let e=null,o=t.required?"required":"";switch(t.type){case"select":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <select id="${l}" class="form-select">
                <option value="">--Sélectionnez--</option>
                ${t.options.map(n=>`<option value="${n}">${n}</option>`).join("")}
              </select>
              <div class="invalid-feedback"></div>
            </div>`;break;case"text":case"textarea":let c="";if(t.attributes)for(const n in t.attributes)c+=`${n}="${t.attributes[n]}" `;e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              ${t.type==="text"?`<input type="text" class="form-control" id="${l}" placeholder="${t.label}"
                  ${o} maxlength="191" ${c}></input>`:`<textarea class="form-control" id="${l}" placeholder="${t.label}"
                  ${o} maxlength="1000"></textarea>`}
              <div class="invalid-feedback"></div>
            </div>
          `;break;case"email":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <input type="email" class="form-control" id="${l}" placeholder="${t.label}"
                ${o} maxlength="191">
              <div class="invalid-feedback"></div>
            </div>
          `;break;case"country":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <select id="${l}" class="form-select" ${o}></select>
              <div class="invalid-feedback"></div>
            </div>
          `;break;case"date":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <input type="date" class="form-control" id="${l}" placeholder="${t.label}"
                ${t.lte_today?`max="${moment().format("YYYY-MM-DD")}"`:""} ${o}>
              <div class="invalid-feedback"></div>
            </div>
          `;break;case"datetime":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <input type="datetime-local" class="form-control" id="${l}" placeholder="${t.label}"
                ${t.lte_today?`max="${moment().format("YYYY-MM-DD HH:mm:ss")}"`:""} ${o}>
              <div class="invalid-feedback"></div>
              </div>
          `;break;case"number":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <input type="number" class="form-control" id="${l}" placeholder="${t.label}" ${o}>
              <div class="invalid-feedback"></div>
            </div>
          `;break;case"card":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <div class="input-group mb-3">
                <select id="${l}_digits" class="form-select flex-grow-0 flex-shrink-0 w-auto">
                  <option value="10" selected>10 chiffres</option>
                  <option value="16">16 chiffres</option>
                </select>
                <input type="tel" pattern="d*" class="form-control" id="${l}" placeholder="xxxxxxxxxx"
                  ${o} minlength="10" maxlength="10">
                <div class="invalid-feedback"></div>
              </div>
            </div>
          `;break;case"file":e=`
            <div class="col-12 col-lg-6 mb-3">
              <label for="${l}" class="form-label">${t.label}</label>
              <input type="file" class="form-control" id="${l}" ${o}>
              <div class="invalid-feedback"></div>
              <div class="form-text update-file-helper">
                Si vous ignorez ce champs, l'ancien fichier sera maintenu
              </div>
            </div>
          `;break}a.before(e)}}function A(a=null,l=""){const t=i&&i.fees&&i.fees[l]||[{breakpoint:"",value:"0"}];for(let e of t)if(e.breakpoint===""||a<=parseFloat(e.breakpoint))if(/^\d+$/.test(e.value)){let o=parseInt(e.value);return a===null?[0,o]:[a-o,o]}else{let o=parseInt(a*parseFloat(e.value.replace(",","."))/100);return[a-o,o]}return[a,0]}function T(a,l=""){const t=i&&i.commissions&&i.commissions[l]||[{breakpoint:"",value:"0"}];for(let e of t)if(e.breakpoint===""||a<=parseFloat(e.breakpoint))return/^\d+$/.test(e.value)?parseInt(e.value):parseInt(a*parseFloat(e.value.replace(",","."))/100);return 0}function s(a=null){const l=f=>{if(f==null)return 0;const _=(""+f).replace(/\s/g,"").replace(/,/g,"."),b=parseFloat(_);return isNaN(b)?0:Math.round(b)};a=a===null?null:l(a);let t,e,o;const c=$("#card_type").val()||"";hasCommissions(USER.master,i.id,c)?([t,e]=A(a,c),o=T(a,c)):(i.code==="card_recharge"?e=0:e=a<=5e5?100:200,t=a<e?0:a-e,o=0);const n=$("#manual_fee"),d=$("#manual_platform_commission");if(n.length&&(n.val()===""||n.val()==null)&&n.val(e),d.length&&(d.val()===""||d.val()==null)){const f=Math.max(e-o,0);d.val(f)}const p=l(n.val()||e||0)||0,v=l(d.val()||Math.max(e-o,0)||0)||0,h=Math.max(p-v,0);$("#opAmount").html(formatAmount(t));const u=l(t||0)+p;$("#opTotalAmount").html(formatAmount(u)),$("#opCommission").html(formatAmount(h)),$("#opRequired").html(formatAmount(u)),$("#opCurrentBalance").html(formatAmount(m))}async function E(){let a=null;const l=e=>e.id?$(`<img src="${getCountryFlagUrl(e.code)}"> <span>${e.name}</span>`):e.text;for(const[e,o]of i.sorted_fields)switch(o.type){case"country":a===null&&(a=(await ajax({url:`${API_BASEURL}/countries`,type:"GET"})).data,a.forEach(c=>c.text=c.name)),$(`#${e}`).select2({data:a,theme:"bootstrap-5",placeholder:"Select a country",allowClear:!0,templateResult:l,templateSelection:l});break;case"card":$(`#${e}_digits`).change(function(){let c=parseInt($(this).val()),n=c===10?10:19;$(`#${e}`).prop({minlength:n,maxlength:n,placeholder:c===10?"xxxxxxxxxx":"xxxx xxxx xxxx xxxx"}).trigger("input")}),$(`#${e}`).on("input",function(){let c=$(this).val().replace(/\D/g,""),n=$(this).prop("maxlength");n===10?c=c.substring(0,10):n===19&&c&&(c=c.match(/.{1,4}/g).join(" ")),$(this).val(c)});break}if($("#card_type").change(function(e){i.amount_field&&s($(`#${i.amount_field}`).val()||0)}),i.code==="account_recharge"&&$("#sender_phone_number_type").change(async function(){$(this).val()==="Autres"?$("#other_type").parent().show():$("#other_type").parent().hide()}).change(),i.code==="card_activation"&&$("#card_id").change(async function(){try{let{data:e}=await ajax({url:`${API_BASEURL}/cards/fetch-by-card-id/${$(this).val()}`,type:"GET"});$("#uba_type").val(e.category.name)}catch({error:e}){console.log(e)}}),i.code==="card_recharge"){if(r&&USER.hasRole("reviewer")){for(const[e,o]of i.sorted_fields)["card_id","client_first_name","client_last_name"].includes(e)===!1&&$("#"+e).parent().hide();$("#card_id").prop("disabled",!0),$("#card_id_digits").prop("disabled",!0)}$("#card_id").change(async function(){try{let{data:e}=await ajax({url:`${API_BASEURL}/card-holders/fetch/${$(this).val()}`,type:"GET"});$("#card_type").val(e.card_type),$("#uba_type").val(e.uba_type),$("#card_four_digits").val(e.card_four_digits),$("#client_first_name").val(e.client_first_name),$("#client_last_name").val(e.client_last_name)}catch({error:e}){console.log(e)}})}i.code==="canal_resub"&&$("#formula").change(async function(){const e=$(this).val().match(/\((\d+)\)/),o=parseInt(e[1]);$("#amount").val(o).prop("disabled",o!==0).keyup()}),$("#form").submit(function(e){e.preventDefault()});const t=()=>{const e=$("#clientType").val();!e||e==="partner"?($("#partnerSelectBlock").show(),$("#manualClientBlock").hide()):($("#partnerSelectBlock").hide(),!$("#requester_name").val()&&USER&&USER.full_name&&$("#requester_name").val(USER.full_name),$("#manualClientBlock").show(),$("#partnerId").val("").change())};$("#clientType").on("change",t),t(),["account_recharge","balance_withdrawal"].includes(i.code)?$("#blockCommissions").hide():i.amount_field?($(`#${i.amount_field}`).keyup(function(){s($(this).val()||0)}),s($(`#${i.amount_field}`).val()||0)):s(0);try{const{data:e}=await ajax({url:`${API_BASEURL}/collabs/me/balance`,type:"GET"});m=parseInt((e==null?void 0:e.balance)??0)||0,$("#opCurrentBalance").html(formatAmount(m))}catch{m=0}if(!["account_recharge","balance_withdrawal"].includes(i.code)){const e=i.amount_field&&$(`#${i.amount_field}`).val()||0;s(e)}$("#manual_fee, #manual_platform_commission").on("input",function(){const e=i.amount_field&&$(`#${i.amount_field}`).val()||0;s(e)}),setTitle(r?`Édition de l'opération ${i.name} ${r.code}`:`Nouvelle opération «${i.name}»`),$("#linkList").html('<i class="fas fa-list"></i> Liste des opérations').attr("href",`/operations/${i.code}`),r?k():y()}window.render=async function(){g(),await x(),S(),w(),await E();const a=$("#clientType");(a.length===0||(a.val()||"partner")==="partner")&&$("#partnerSelectBlock").show()};
