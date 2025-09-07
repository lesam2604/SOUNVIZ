window.render = async function () {
  $('#form').submit(async function (e) {
    e.preventDefault();

    swalLoading();

    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/reset-password`,
        type: 'POST',
        data: {
          email: $('#email').val(),
          token: $('#token').val(),
          password: $('#password').val(),
          password_confirmation: $('#confirmPassword').val(),
        },
      });

      await Swal.fire(data.message, '', 'success');

      localStorage.setItem('token', data.authorization.token);

      for (const role of ['admin', 'collab', 'partner']) {
        if (data.user.roles.indexOf(role) !== -1) {
          setCookie('user-type', role);
          break;
        }
      }

      location.href = '/dashboard';
    } catch ({ error }) {
      console.log(error);
      if (error.responseJSON.errors) {
        Swal.close();
      }

      showErrors(error.responseJSON, {
        email: '#password,#confirmPassword',
        token: '#password,#confirmPassword',
        password: '#password',
        password_confirmation: '#confirmPassword',
      });
    }
  });
};
