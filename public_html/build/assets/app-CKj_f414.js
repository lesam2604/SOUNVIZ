(async function(){if(!localStorage.getItem("token")){location.replace("/login");return}try{let[s,t]=await Promise.all([getUser(),getSettings()]);USER.set(s),SETTINGS=t}catch(s){return console.log(s),location.replace("/login")}$(async()=>{SETTINGS.opTypes.forEach(e=>{$("#cardManagementTitle").before(`
        <li class="sidebar-item has-sub">
          <a href="#" class="sidebar-link">
            <i class="${e.icon_class}"></i>
            <span>${e.name}</span>
          </a>
          <ul class="submenu">
            ${USER.hasRole("partner-pos")&&["balance_withdrawal"].includes(e.code)?"":`
                <li class="submenu-item" data-permission="add operation">
                  <a href="/operations/${e.code}/create"><i class="fas fa-plus"></i> Nouvelle</a>
                </li>
              `}
            <li class="submenu-item">
              <a href="/operations/${e.code}/pending"><i class="fas fa-clock"></i> En attente</a>
            </li>
            <li class="submenu-item">
              <a href="/operations/${e.code}/approved"><i class="fas fa-thumbs-up"></i> Validées</a>
            </li>
            <li class="submenu-item">
              <a href="/operations/${e.code}/rejected"><i class="fas fa-thumbs-down"></i> Rejetées</a>
            </li>
          </ul>
        </li>
      `)}),$("body").append('<script src="/assets/js/app.js"><\/script>'),$("[data-role]").each(function(){let e=$(this).attr("data-role").split("|");for(const a of e)if(USER.hasRole(a))return;$(this).hide()}),$("[data-permission]").each(function(){const e=$(this).attr("data-permission").split("|");if(!(e.includes("add operation")&&(USER.hasRole("admin")||USER.hasRole("collab")))){for(const a of e)if(USER.can(a))return;$(this).hide()}}),$(".menu>.sidebar-item>a.sidebar-link, .submenu>.submenu-item>a").each(function(e){$(this).attr("href")===location.pathname&&$(this).parents(".sidebar-item, .submenu, .submenu-item").addClass("active")}),$(document).on("input",'input:not([type="email"], [type="password"])',function(e){$(this).val($(this).val().toUpperCase())}),USER.hasRole("partner")&&$("#partner-balance").text(formatAmount(USER.balance)),$("#changePassword").click(function(e){e.preventDefault(),$("#modalChangePassword").modal("show")}),$("#btnChangePassword").click(async function(e){try{let{data:a}=await ajax({url:`${API_BASEURL}/change-password`,type:"POST",data:{password:$("#password").val(),new_password:$("#newPassword").val(),new_password_confirmation:$("#confirmNewPassword").val()}});Swal.fire(a.message,"","success"),$("#modalChangePassword").modal("hide")}catch({error:a}){console.log(a),a.responseJSON.errors&&Swal.close(),showErrors(a.responseJSON,{password:"#password",new_password:"#newPassword",new_password_confirmation:"#confirmNewPassword"})}}),$("#logout").click(async function(e){e.preventDefault();try{await logout(),localStorage.clear(),deleteCookie("user-type"),location.href="/login"}catch(a){Swal.fire(a,"","error")}}),$(".user-full-name").html(`${USER.first_name} ${USER.last_name}`),$(".user-first-name").html(USER.first_name),$(".user-type").html((()=>{if(USER.hasRole("admin"))return"Administrateur";if(USER.hasRole("collab"))return"Collaborateur";if(USER.hasRole("partner-master"))return USER.partner.company.name.trim()?`Partenaire (${USER.partner.company.name.trim()})`:"Partenaire";if(USER.hasRole("partner-pos"))return USER.partner.company.name.trim()?`Boutique (${USER.partner.company.name.trim()})`:"Boutique"})()),$(".user-email").html(USER.email),$(".user-picture").attr("src",getThumbnailUrl(USER.picture));const s=async()=>{try{let{data:{notifications:e,tickets:a,broadcastMessages:r}}=await ajax({url:`${API_BASEURL}/users/unseens`,type:"GET"});e.length?$("#badgeNotifications").html(e.length).show():$("#badgeNotifications").hide(),a.length?$("#badgeTickets").html(a.length).show():$("#badgeTickets").hide(),r.length?$("#badgeBroadcastMessages").html(r.length).show():$("#badgeBroadcastMessages").hide()}catch({error:e}){Swal.fire(e.responseJSON.message,"","error")}finally{setTimeout(s,3e3)}};async function t(){let{data:e}=await ajax({url:`${API_BASEURL}/scrolling-messages/fetch-visibles`,type:"GET",data:{target:"app"}});e.forEach(a=>{renderScrollingMessageHtml(a).appendTo("#containerScrollingMessages")}),$(":root").css("--marquee-init-x",$("#containerScrollingMessages").width()+"px")}try{await Promise.all([s(),t(),typeof render=="function"?render():Promise.resolve()])}catch(e){console.log(e)}hidePreloader()})})();
