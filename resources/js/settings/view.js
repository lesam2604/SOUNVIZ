function populateSettings() {
  let counter = 0;

  const liOthers = $('#tabOthers').closest('li');
  const paneOthers = $('#paneOthers');

  for (const opType of SETTINGS.opTypes) {
    if (['account_recharge', 'balance_withdrawal'].includes(opType.code))
      continue;

    for (const cardType in opType.fees) {
      liOthers.before(`
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="tab-${opType.code}-${cardType}"
            data-bs-toggle="tab" role="tab"
            href="#pane-${opType.code}-${cardType}"
            aria-controls="pane-${opType.code}-${cardType}"
            aria-selected="false">
            ${opType.name}${cardType ? ` (${cardType})` : ''}
          </a>
        </li>
      `);

      let opTypePaneRow = $(`
        <div class="tab-pane fade" role="tabpanel"
          id="pane-${opType.code}-${cardType}"
          aria-labelledby="tab-${opType.code}-${cardType}">
          <div class="row">
            <h4 class="text-center">
              ${opType.name}${cardType ? ` (${cardType})` : ''}
            </h4>
            <hr>
          </div>
        </div>
      `)
        .insertBefore(paneOthers)
        .find('.row');

      [
        ['fees', 'Frais de courses'],
        ['commissions', 'Commissions du partenaire'],
      ].forEach(([valueType, valueTitle]) => {
        const valueContainer = opTypePaneRow
          .append(
            `<div class="col-12 col-lg-6">
            <div class="${valueType}-container">
              <h5>${valueTitle}</h5>
              <hr>
            </div>
          </div>`
          )
          .find(`.${valueType}-container`);

        opType[valueType][cardType].forEach((step) => {
          valueContainer.append(`
            <div class="step row mb-3 ms-3 pb-3 border-bottom align-items-end">
              <div class="col-12 col-lg-5 mb-3">
                <label for="${++counter}Breakpoint" class="form-label">Seuil</label>
                <input type="number" class="form-control breakpoint" id="${counter}Breakpoint"
                  placeholder="Infinie" value="${step.breakpoint}">
              </div>
              <div class="col-12 col-lg-5 mb-3">
                <label for="${counter}Value" class="form-label">Valeur</label>
                <input type="text" class="form-control value" id="${counter}Value" required"
                  placeholder="Valeur à appliquer" value="${step.value}">
              </div>
              <div class="col-12 col-lg-2 mb-3">
                <button type="button" class="btn btn-primary step-add"><i class="fas fa-plus"></i></button>
                <button type="button" class="btn btn-danger step-remove"><i class="fas fa-minus"></i></button>
              </div>
            </div>
          `);
        });
      });

      $(`
        <div class="text-center">
          <button type="button" class="btn btn-primary btn-lg save-settings"><i class="fas fa-save"></i>
            Sauvegarder</button>
        </div>
      `)
        .appendTo(opTypePaneRow)
        .find('.save-settings')
        .data({ opType, cardType });
    }
  }

  $('#dashboardMessage').val(SETTINGS.dashboardMessage);

  $('#myTab .nav-link').first().tab('show');
}

async function updateDashboardMessage() {
  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/settings/update-dashboard-message`,
      type: 'POST',
      data: {
        dashboard_message: $('#dashboardMessage').val(),
      },
    });
    Toast.fire(data.message, '', 'success');
  } catch ({ error }) {
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

async function saveSettings(opType, cardType) {
  const opTypeData = {};
  const tabPane = $(`#pane-${opType.code}-${cardType}`);

  ['fees', 'commissions'].forEach((valueType) => {
    opTypeData[valueType] = [];

    tabPane.find(`.${valueType}-container .step`).each(function () {
      const step = $(this);

      opTypeData[valueType].push({
        breakpoint: step.find('.breakpoint').val(),
        value: step.find('.value').val(),
      });
    });
  });

  swalLoading();

  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/settings`,
      type: 'POST',
      data: {
        operation_type_id: opType.id,
        card_type: cardType,
        op_type_data: JSON.stringify(opTypeData),
      },
    });
    Toast.fire(data.message, '', 'success');
  } catch ({ error }) {
    console.log(error);
    Swal.fire(error.responseJSON.message, '', 'error');
  }
}

window.render = function () {
  populateSettings();

  $('#updateDashboardMessage').click(function (e) {
    updateDashboardMessage();
  });

  $('#tabPanes')
    .on('click', '.step-add', function () {
      const step = $(this).closest('.step');
      const newStep = step.clone();

      newStep.find('.breakpoint').val('');
      newStep.find('.value').val('');

      newStep.insertAfter(step);
    })
    .on('click', '.step-remove', function () {
      const step = $(this).closest('.step');

      if (!step.siblings('.step').length) {
        Toast.fire(
          'Impossible de supprimer la seule valeur disponible',
          '',
          'error'
        );
        return;
      }

      step.remove();
    })
    .on('click', '.save-settings', function () {
      saveSettings($(this).data('opType'), $(this).data('cardType'));
    });
};
