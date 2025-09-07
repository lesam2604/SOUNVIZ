let object = null;

async function fetchObject() {
  let objectId = $('#objectId').val();

  if (objectId) {
    try {
      let { data } = await ajax({
        url: `${API_BASEURL}/cards/fetch/${objectId}`,
        type: 'GET',
      });

      object = data;
    } catch ({ error }) {
      await Swal.fire(error.responseJSON.message, '', 'error');
      location = '/cards';
    }
  }
}

async function populateCategories() {
  try {
    let { data: categories } = await ajax({
      url: `${API_BASEURL}/card-categories/fetch-all`,
      type: 'GET',
    });

    $('#cardCategoryId').empty();

    for (const category of categories) {
      $('#cardCategoryId').append(
        `<option value="${category.id}">${category.name}</option>`
      );
    }
  } catch ({ error }) {
    await Swal.fire(error.responseJSON.message, '', 'error');
    location = '/cards';
  }
}

function clearForm() {
  $('#cardCategoryId').val('');
  clearCardAddingTypes();
  $('.is-invalid').removeClass('is-invalid');
}

function setForm() {
  $('#cardCategoryId').val(object.card_category_id).attr('disabled', true);
  $('#type').val('one').change().parent().hide();
  $('#cardId').val(object.card_id);
}

async function createObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/cards/store`,
      type: 'POST',
      data: fillCardAddingTypes({
        card_category_id: $('#cardCategoryId').val(),
      }),
    });

    Toast.fire(data.message, '', 'success');
    clearForm();
  } catch ({ error }) {
    console.log(error);
    if (error.responseJSON.errors) {
      Swal.close();
    }

    showErrors(
      error.responseJSON,
      fillCardAddingErrorFields({
        card_category_id: '#cardCategoryId',
      })
    );
  }
}

async function updateObject() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/cards/update/${object.id}`,
      type: 'POST',
      data: {
        card_category_id: $('#cardCategoryId').val(),
        card_id: $('#cardId').val(),
      },
    });

    Toast.fire(data.message, '', 'success');
    location = `/cards/${object.id}`;
  } catch ({ error }) {
    console.log(error);
    viewErrors(error.responseJSON.errors);
  }
}

window.render = async function () {
  await Promise.all([fetchObject(), populateCategories()]);

  initCardAddingTypes();

  setTitle(
    object ? `Édition de la carte ${object.card_id}` : 'Nouvelles cartes'
  );

  object ? setForm() : clearForm();

  $('#form').submit(function (e) {
    e.preventDefault();
    object ? updateObject() : createObject();
  });
};
