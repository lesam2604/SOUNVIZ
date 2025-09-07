let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/scrolling-messages/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/scrolling-messages';
    }
  }
}

function clearForm() {
  $('#label').val('');
  $('#content').val('');
  $('#from').val('');
  $('#to').val('');
  $('#time').val('0');
  $('#size').val('');
  $('#color').val('');
  $('#show_auth').val('0');
  $('#show_app').val('0');
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#label').val(object.label);
  $('#content').val(object.content);
  $('#from').val(object.from ? moment(object.from).format('YYYY-MM-DD') : '');
  $('#to').val(object.to ? moment(object.to).format('YYYY-MM-DD') : '');
  $('#time').val(object.time);
  $('#size').val(object.size);
  $('#color').val(object.color);
  $('#show_auth').val(object.show_auth);
  $('#show_app').val(object.show_app);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/scrolling-messages/store`,
      type: 'POST',
      data: {
        label: $('#label').val(),
        content: $('#content').val(),
        from: $('#from').val(),
        to: $('#to').val(),
        time: $('#time').val(),
        size: $('#size').val(),
        color: $('#color').val(),
        show_auth: $('#show_auth').val(),
        show_app: $('#show_app').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON);
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/scrolling-messages/update/${object.id}`,
      type: 'POST',
      data: {
        label: $('#label').val(),
        content: $('#content').val(),
        from: $('#from').val(),
        to: $('#to').val(),
        time: $('#time').val(),
        size: $('#size').val(),
        color: $('#color').val(),
        show_auth: $('#show_auth').val(),
        show_app: $('#show_app').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/scrolling-messages/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(error.responseJSON);
  }
}

window.render = async function () {
  await fetchObject();
  setTitle(object ? `Édition du message «${object.label}»` : 'Nouveau message');

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
