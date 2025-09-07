window.dataTableCardIds = null;

window.clearCardAddingTypes = function () {
  $('#type').val('one').change().parent().show();
  $('#cardId').val('');
  $('#cardIdMany').val('');
  dataTableCardIds.clear().draw();
  $('#cardIdFrom').val('');
  $('#cardIdTo').val('');
};

window.fillCardAddingTypes = function (data) {
  data.type = $('#type').val();

  if (data.type === 'one') {
    data.card_id = $('#cardId').val();
  } else if (data.type === 'many') {
    data.card_ids = dataTableCardIds
      .rows()
      .data()
      .toArray()
      .map((row) => row.card_id);
  } else if (data.type === 'range') {
    data.card_id_from = $('#cardIdFrom').val();
    data.card_id_to = $('#cardIdTo').val();
  }

  return data;
};

window.fillCardAddingErrorFields = function (errorFields) {
  errorFields.type = '#type';
  errorFields.card_id = '#cardId';
  errorFields.card_ids = '#cardIdMany';
  errorFields.card_id_from = '#cardIdFrom';
  errorFields.card_id_to = '#cardIdTo';

  return errorFields;
};

window.initDataTableCardIds = function () {
  dataTableCardIds = $('#tableCardIds')
    .on('click', '.remove', function (e) {
      dataTableCardIds.row($(this).closest('tr')).remove().draw(false);
    })
    .DataTable({
      processing: true,
      columns: [
        {
          data: 'card_id',
        },
        {
          orderable: false,
          render: (data, type, row) => {
            return `
              <button type="button" class="btn btn-sm btn-danger m-1 remove"><i class="fas fa-times"></i> Retirer</button>
            `;
          },
        },
      ],
      order: [0, 'desc'],
      pageLength: 25,
      autoWidth: false,
    });
};

window.initCardAddingTypes = function () {
  initDataTableCardIds();

  $('#addCardId').click(function (e) {
    let cardId = $('#cardIdMany').val();

    if (cardId.length !== 10) {
      Swal.fire("L'id doit être composé de 10 chiffres", '', 'error');
      return;
    }

    let matchingRows = dataTableCardIds
      .rows()
      .data()
      .filter((row) => {
        return row.card_id === cardId;
      });

    if (matchingRows.length > 0) {
      Swal.fire('Cet id est déjà dans la liste', '', 'error');
      return;
    }

    dataTableCardIds.row.add({ card_id: cardId }).draw(false);
  });

  $('#type').change(function (e) {
    let types = ['one', 'many', 'range'];

    for (const type of types) {
      if (type === $(this).val()) {
        $(`#${type}Block`).show();
      } else {
        $(`#${type}Block`).hide();
      }
    }
  });

  $('#cardId, #cardIdMany, #cardIdFrom, #cardIdTo').on('input', function () {
    let value = $(this).val().replace(/\D/g, '').substring(0, 10);
    $(this).val(value);
  });
};
