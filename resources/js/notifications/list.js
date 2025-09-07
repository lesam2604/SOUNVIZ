async function loadMore() {
  $('#loadMore').html('Chargement en cours...');

  try {
    let { data: notifications } = await ajax({
      url: `${API_BASEURL}/notifications`,
      type: 'GET',
      data: {
        before_id:
          $('#notifications .notification-list:last-child').data('id') ?? '',
        length: 50,
        seen: '',
      },
    });

    for (const notification of notifications) {
      $('#notifications').append(`
        <div class="notification-list ${
          notification.seen_at ? '' : 'notification-list--unread'
        }" data-id="${notification.id}">
          <div class="notification-list_content">
            <div class="notification-list_img d-flex align-items-center">
              <i class="${notification.icon_class}"></i>
            </div>
            <div class="notification-list_detail">
              <p class="fw-bold subject mb-3">
                <a href="${notification.link}" class="notification-link">
                  ${notification.subject}</a>
              </p>
              <p class="text-muted mb-3">${notification.body}</p>
              <p class="text-muted"><small>${getTimeAgo(
                notification.created_at
              )}</small></p>
            </div>
          </div>
        </div>
      `);
    }
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }

  $('#loadMore').html(`<i class="fas fa-sync-alt"></i> Charger plus`);
}

window.render = function () {
  $('#loadMore')
    .click(function (e) {
      loadMore();
    })
    .click();

  $('#notifications').on('click', '.notification-link', async function (e) {
    e.preventDefault();

    let not = $(this).closest('.notification-list');

    if (not.hasClass('notification-list--unread')) {
      await ajax({
        url: `${API_BASEURL}/notifications/mark-as-seen/${not.data('id')}`,
        type: 'POST',
      });
    }

    location = $(this).attr('href');
  });
};
