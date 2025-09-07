(async function () {
  if (!localStorage.getItem('token')) {
    location.replace('/login');
    return;
  }

  try {
    let [user, settings] = await Promise.all([getUser(), getSettings()]);
    USER.set(user);
    SETTINGS = settings;
  } catch (errorMessage) {
    console.log(errorMessage);
    return location.replace('/login');
  }

  $(async () => {
    SETTINGS.opTypes.forEach((opType) => {
      $('#cardManagementTitle').before(`
        <li class="sidebar-item has-sub">
          <a href="#" class="sidebar-link">
            <i class="${opType.icon_class}"></i>
            <span>${opType.name}</span>
          </a>
          <ul class="submenu">
            ${
              USER.hasRole('partner-pos') && ['balance_withdrawal'].includes(opType.code)
                ? ''
                : `
                <li class="submenu-item" data-permission="add operation">
                  <a href="/operations/${opType.code}/create"><i class="fas fa-plus"></i> Nouvelle</a>
                </li>
              `
            }
            <li class="submenu-item">
              <a href="/operations/${
                opType.code
              }/pending"><i class="fas fa-clock"></i> En attente</a>
            </li>
            <li class="submenu-item">
              <a href="/operations/${
                opType.code
              }/approved"><i class="fas fa-thumbs-up"></i> Validées</a>
            </li>
            <li class="submenu-item">
              <a href="/operations/${
                opType.code
              }/rejected"><i class="fas fa-thumbs-down"></i> Rejetées</a>
            </li>
          </ul>
        </li>
      `);
    });

    $('body').append(`<script src="/assets/js/app.js"></script>`);

    $('[data-role]').each(function () {
      let roles = $(this).attr('data-role').split('|');
      for (const role of roles) if (USER.hasRole(role)) return;
      $(this).hide();
    });

    $('[data-permission]').each(function () {
      const permissions = $(this).attr('data-permission').split('|');
      // Laisser visible "add operation" pour admin et collab même si l'autorisation n'est pas explicitement mappée
      if (permissions.includes('add operation') && (USER.hasRole('admin') || USER.hasRole('collab'))) {
        return;
      }
      for (const permission of permissions) if (USER.can(permission)) return;
      $(this).hide();
    });

    $('.menu>.sidebar-item>a.sidebar-link, .submenu>.submenu-item>a').each(
      function (e) {
        if ($(this).attr('href') === location.pathname) {
          $(this)
            .parents('.sidebar-item, .submenu, .submenu-item')
            .addClass('active');
        }
      }
    );

    $(document).on(
      'input',
      'input:not([type="email"], [type="password"])',
      function (e) {
        $(this).val($(this).val().toUpperCase());
      }
    );

    if (USER.hasRole('partner')) {
      $('#partner-balance').text(formatAmount(USER.balance));
    }

    $('#changePassword').click(function (e) {
      e.preventDefault();
      $('#modalChangePassword').modal('show');
    });

    $('#btnChangePassword').click(async function (e) {
      try {
        let { data } = await ajax({
          url: `${API_BASEURL}/change-password`,
          type: 'POST',
          data: {
            password: $('#password').val(),
            new_password: $('#newPassword').val(),
            new_password_confirmation: $('#confirmNewPassword').val(),
          },
        });

        Swal.fire(data.message, '', 'success');
        $('#modalChangePassword').modal('hide');
      } catch ({ error }) {
        console.log(error);
        if (error.responseJSON.errors) {
          Swal.close();
        }

        showErrors(error.responseJSON, {
          password: '#password',
          new_password: '#newPassword',
          new_password_confirmation: '#confirmNewPassword',
        });
      }
    });

    $('#logout').click(async function (e) {
      e.preventDefault();

      try {
        await logout();
        localStorage.clear();
        deleteCookie('user-type');
        location.href = '/login';
      } catch (errorMessage) {
        Swal.fire(errorMessage, '', 'error');
      }
    });

    $('.user-full-name').html(`${USER.first_name} ${USER.last_name}`);
    $('.user-first-name').html(USER.first_name);
    $('.user-type').html(
      (() => {
        if (USER.hasRole('admin')) {
          return 'Administrateur';
        } else if (USER.hasRole('collab')) {
          return 'Collaborateur';
        } else if (USER.hasRole('partner-master')) {
          if (USER.partner.company.name.trim())
            return `Partenaire (${USER.partner.company.name.trim()})`;
          return 'Partenaire';
        } else if (USER.hasRole('partner-pos')) {
          if (USER.partner.company.name.trim())
            return `Boutique (${USER.partner.company.name.trim()})`;
          return 'Boutique';
        }
      })()
    );
    $('.user-email').html(USER.email);
    $('.user-picture').attr('src', getThumbnailUrl(USER.picture));

    const loadUnseens = async () => {
      try {
        let {
          data: { notifications, tickets, broadcastMessages },
        } = await ajax({
          url: `${API_BASEURL}/users/unseens`,
          type: 'GET',
        });

        if (notifications.length) {
          $('#badgeNotifications').html(notifications.length).show();
        } else {
          $('#badgeNotifications').hide();
        }

        if (tickets.length) {
          $('#badgeTickets').html(tickets.length).show();
        } else {
          $('#badgeTickets').hide();
        }

        if (broadcastMessages.length) {
          $('#badgeBroadcastMessages').html(broadcastMessages.length).show();
        } else {
          $('#badgeBroadcastMessages').hide();
        }
      } catch ({ error }) {
        Swal.fire(error.responseJSON.message, '', 'error');
      } finally {
        setTimeout(loadUnseens, 3000);
      }
    };

    async function getScrollingMessage() {
      let { data: messages } = await ajax({
        url: `${API_BASEURL}/scrolling-messages/fetch-visibles`,
        type: 'GET',
        data: {
          target: 'app',
        },
      });

      messages.forEach((message) => {
        renderScrollingMessageHtml(message).appendTo(
          '#containerScrollingMessages'
        );
      });

      $(':root').css(
        '--marquee-init-x',
        $('#containerScrollingMessages').width() + 'px'
      );
    }

    try {
      await Promise.all([
        loadUnseens(),
        getScrollingMessage(),
        typeof render === 'function' ? render() : Promise.resolve(),
      ]);
    } catch (error) {
      console.log(error);
    }

    hidePreloader();
  });
})();
