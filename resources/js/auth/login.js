async function getScrollingMessage() {
  let { data: messages } = await ajax({
    url: `${API_BASEURL}/scrolling-messages/fetch-visibles`,
    type: 'GET',
  });

  messages.forEach((message) => {
    renderScrollingMessageHtml(message).appendTo('#containerScrollingMessages');
  });

  $(':root').css(
    '--marquee-init-x',
    $('#containerScrollingMessages').width() + 'px'
  );
}

window.render = async function () {
  await getScrollingMessage();

  $('#form').submit(async function (e) {
    e.preventDefault();

    $('#form button')
      .html('Connexion en cours <i class="fas fa-spinner fa-pulse"></i>')
      .prop('disabled', true);

    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/login`,
        type: 'POST',
        data: {
          email: $('#email').val(),
          password: $('#password').val(),
        },
      });

      localStorage.setItem('token', data.authorization.token);

      for (const role of ['admin', 'collab', 'partner']) {
        if (data.user.roles.indexOf(role) !== -1) {
          setCookie('user-type', role);
          break;
        }
      }

      location.href = '/dashboard';
    } catch ({ error }) {
      $('#form button').html('Se connecter').prop('disabled', false);
      console.log(error);
      showErrors(error.responseJSON);
    }
  });
};
