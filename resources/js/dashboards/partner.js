function generateOperationGroups() {
  let groups = [
    ['Opérations en attente', 'pending', 'blue'],
    ['Opérations validées', 'approved', 'green'],
    ['Opérations rejetées', 'rejected', 'red'],
  ];

  for (const [label, status, color] of groups) {
    let groupRow = $(`<div class="row"><h5>${label}</h5></div>`);

    for (const opType of SETTINGS.opTypes) {
      groupRow.append(`
        <div class="col-12 col-lg-4 col-md-6">
          <div class="card">
            <div class="card-body px-4 py-4">
              <div class="row">
                <div class="col-3 d-flex justify-content-start">
                  <div class="stats-icon ${color} mb-2">
                    <i class="${opType.icon_class}"></i>
                  </div>
                </div>
                <div class="col-9">
                  <h6 class="text-muted font-semibold">${opType.name}</h6>
                  <h6 class="font-extrabold mb-0" id="${opType.code}_${status}">0</h6>
                  <div class="text-end">
                    <a href="/operations/${opType.code}/${status}" title="Acceder a la liste"
                      class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> 
      `);
    }

    groupRow.insertBefore('#otherOps');
  }
}

function renderChartOperationStatus(data) {
  let sum = {
    pending: 0,
    approved: 0,
    rejected: 0,
  };

  for (const opType of SETTINGS.opTypes) {
    for (const status of ['pending', 'approved', 'rejected']) {
      sum[status] += data[`${opType.code}_${status}`];
    }
  }

  let options = {
    series: [sum.pending, sum.approved, sum.rejected],
    labels: ['En attente', 'Validées', 'Rejetées'],
    colors: ['#57caeb', '#5ddab4', '#ff7976'],
    chart: {
      type: 'donut',
      width: '100%',
      height: '350px',
    },
    legend: {
      position: 'bottom',
    },
    plotOptions: {
      pie: {
        donut: {
          size: '30%',
        },
      },
    },
  };

  let chart = new ApexCharts(
    document.getElementById('chartOperationStatus'),
    options
  );
  chart.render();
}

async function loadData() {
  let { data } = await ajax({
    url: `${API_BASEURL}/partners/dashboard-data`,
    type: 'GET',
  });

  // Display numbers
  const formatter = new Intl.NumberFormat('fr-FR');

  for (const key in data) {
    if (key !== 'histories') {
      $('#' + key).text(formatter.format(data[key]));
    }
  }

  // Display activities
  let tbody = $('#tableActivities tbody').empty();

  for (const his of data.histories) {
    tbody.append(`
       <tr title="${his.content}">
         <td class="col-12">
           <div class="d-flex align-items-center">
             <div class="avatar avatar-md">
               <img src="${getThumbnailUrl(USER.picture)}">
             </div>
             <p class="font-bold ms-3 mb-0">${his.title}</p>
           </div>
         </td>
       </tr>
     `);
  }

  renderChartOperationStatus(data);
}

async function refreshCardsTotal() {
  let { data } = await ajax({
    url: `${API_BASEURL}/cards/total-stock`,
    type: 'POST',
    data: {},
  });

  $('#allCards').text(data.total);
  $('#activatedCards').text(data.activated ?? 0);
  $('#notActivatedCards').text(data.not_activated ?? 0);
}

async function refreshDecodersTotal() {
  let { data } = await ajax({
    url: `${API_BASEURL}/decoders/total-stock`,
    type: 'POST',
    data: {},
  });

  $('#allDecoders').text(data.total);
  $('#activatedDecoders').text(data.activated ?? 0);
  $('#notActivatedDecoders').text(data.not_activated ?? 0);
}

window.render = async function () {
  generateOperationGroups();

  await Promise.all([
    loadData(),
    USER.hasRole('partner-master') ? refreshCardsTotal() : Promise.resolve(),
    USER.hasRole('partner-master') ? refreshDecodersTotal() : Promise.resolve(),
  ]);

  $('#dashboardMessage').html(SETTINGS.dashboardMessage);
};
