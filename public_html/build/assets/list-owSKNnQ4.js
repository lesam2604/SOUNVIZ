async function a(){$("#loadMore").html("Chargement en cours...");try{let{data:t}=await ajax({url:`${API_BASEURL}/notifications`,type:"GET",data:{before_id:$("#notifications .notification-list:last-child").data("id")??"",length:50,seen:""}});for(const i of t)$("#notifications").append(`
        <div class="notification-list ${i.seen_at?"":"notification-list--unread"}" data-id="${i.id}">
          <div class="notification-list_content">
            <div class="notification-list_img d-flex align-items-center">
              <i class="${i.icon_class}"></i>
            </div>
            <div class="notification-list_detail">
              <p class="fw-bold subject mb-3">
                <a href="${i.link}" class="notification-link">
                  ${i.subject}</a>
              </p>
              <p class="text-muted mb-3">${i.body}</p>
              <p class="text-muted"><small>${getTimeAgo(i.created_at)}</small></p>
            </div>
          </div>
        </div>
      `)}catch({error:t}){Swal.fire(t.responseJSON.message,"","error")}$("#loadMore").html('<i class="fas fa-sync-alt"></i> Charger plus')}window.render=function(){$("#loadMore").click(function(t){a()}).click(),$("#notifications").on("click",".notification-link",async function(t){t.preventDefault();let i=$(this).closest(".notification-list");i.hasClass("notification-list--unread")&&await ajax({url:`${API_BASEURL}/notifications/mark-as-seen/${i.data("id")}`,type:"POST"}),location=$(this).attr("href")})};
