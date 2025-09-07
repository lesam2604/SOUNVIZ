let object = null;
let editorContent = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/broadcast-messages/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/broadcast-messages';
    }
  }
}

function clearForm() {
  $('#label').val('');
  editorContent.root.innerHTML = '';
  $('#group').val('all');
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#label').val(object.label);
  editorContent.root.innerHTML = object.content;
  $('#group').val(object.group);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/broadcast-messages/store`,
      type: 'POST',
      data: {
        label: $('#label').val(),
        content: editorContent.root.innerHTML,
        group: $('#group').val(),
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
      url: `${API_BASEURL}/broadcast-messages/update/${object.id}`,
      type: 'POST',
      data: {
        label: $('#label').val(),
        content: editorContent.root.innerHTML,
        group: $('#group').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/broadcast-messages/${object.id}`;
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

  editorContent = new Quill('#content', {
    // bounds: "#full-container .editor",
    modules: {
      toolbar: [
        [{ font: [] }, { size: [] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ script: 'super' }, { script: 'sub' }],
        [
          { list: 'ordered' },
          { list: 'bullet' },
          { indent: '-1' },
          { indent: '+1' },
        ],
        ['direction', { align: [] }],
        // ['link', 'image', 'video'],
        ['clean'],
      ],
    },
    theme: 'snow',
    placeholder: 'Entrez le contenu de votre message ici',
  });

  setTitle(
    object
      ? `Édition du message de diffusion «${object.label}»`
      : 'Nouveau message de diffusion'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
