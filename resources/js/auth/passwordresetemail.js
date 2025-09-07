window.render = async function () {
  $('#form').submit(async function (e) {
    e.preventDefault();

    swalLoading();

    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/send-password-reset-code`,
        type: 'POST',
        data: {
          email: $('#email').val(),
        },
      });

      Swal.fire(data.message, '', 'success');
    } catch ({ error }) {
      console.log(error);
      if (error.responseJSON.errors) {
        Swal.close();
      }

      showErrors(error.responseJSON);
    }
  });
};
