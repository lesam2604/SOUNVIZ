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
    url: `${API_BASEURL}/collabs/dashboard-data`,
    type: 'GET',
  });

  // Display numbers
  const formatter = new Intl.NumberFormat('fr-FR');

  for (const key in data) {
    if (
      ['to_supply_products', 'histories', 'recent_partners'].includes(key) ===
      false
    ) {
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

  // Display partners
  let containerRecentPartners = $('#recentPartners').empty();

  for (const partner of data.recent_partners) {
    containerRecentPartners.append(`
      <div class="recent-message d-flex px-4 py-3">
        <div class="avatar avatar-lg">
          <img src="${getThumbnailUrl(partner.user.picture)}">
        </div>
        <div class="name ms-4">
          <h5 class="mb-1">${
            partner.user.first_name + ' ' + partner.user.last_name
          }</h5>
          <h6 class="text-muted mb-0">${partner.user.email}</h6>
        </div>
      </div>
    `);
  }

  renderChartOperationStatus(data);
}

function initNewOperationBlock() {
  try {
    const isAllowed = USER.hasRole('admin') || USER.hasRole('collab');
    if (!isAllowed) {
      $('#newOperationBlock').hide();
      return;
    }
    const $sel = $('#newOpType');
    if (!$sel.length) return;
    $sel.empty().append(`<option value="">Sélectionner un type...</option>`);
    (SETTINGS.opTypes || []).forEach(t => {
      $sel.append(`<option value="${t.code}">${t.name}</option>`);
    });
    $sel.on('change', function(){
      const code = $(this).val();
      if (code) {
        $('#newOpBtn').removeClass('disabled').attr('href', `/operations/${code}/create`);
      } else {
        $('#newOpBtn').addClass('disabled').attr('href', '#');
      }
    });
  } catch (e) { console.warn('initNewOperationBlock', e); }
}

window.render = async function () {
  generateOperationGroups();
  await loadData();
  $('#dashboardMessage').html(SETTINGS.dashboardMessage);
  initNewOperationBlock();
};





// === Solde collaborateur (SELF) =============================================
/*async function loadMyBalance() {
  try {
    const res = await fetch('/me/balance', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    if (data && data.ok) {
      // Utilise tes helpers si disponibles
      const formatted = (typeof formatAmountSpaced === 'function')
        ? formatAmountSpaced(data.balance)
        : (parseInt(data.balance, 10).toLocaleString('fr-FR') + ' FCFA');

      const currency = data.currency || 'XOF';

      const $val = document.getElementById('balanceValue');
      const $cur = document.getElementById('balanceCurrency');
      const $hint = document.getElementById('balanceHint');

      if ($val) $val.textContent = formatted;
      if ($cur) $cur.textContent = currency;
      if ($hint) { $hint.style.display = 'inline'; }

    } else {
      // fallback en cas d'erreur logique
      const $val = document.getElementById('balanceValue');
      if ($val) $val.textContent = 'Erreur';
    }
  } catch (e) {
    console.error('[dashboard.collab] loadMyBalance error:', e);
    const $val = document.getElementById('balanceValue');
    if ($val) $val.textContent = 'Erreur';
  }
}

// Boot automatique si la carte est présente
(function () {
  if (document.readyState !== 'loading') {
    if (document.getElementById('balanceValue')) loadMyBalance();
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      if (document.getElementById('balanceValue')) loadMyBalance();
    }, { once: true });
  }
})();
*/

// --- Mon solde (dashboard collab) ---
/*(async function loadMyBalance() {
  try {
    // helpers.js ajoute déjà Authorization: Bearer <token> pour API_BASEURL
    const res = await $.get(`${API_BASEURL}/collabs/me/balance`);
    if (res?.ok) {
      const amount = Number(res.balance || 0);
      $('#myBalanceAmount').text(
        amount.toLocaleString('fr-FR') + ' ' + (res.currency || 'FCFA')
      );
      $('#myBalanceCurrency').text(res.currency || 'FCFA');
    } else {
      console.warn('Balance API response not ok:', res);
    }
  } catch (e) {
    console.error('Erreur loadMyBalance:', e);
  }
})();*/
// --- Mon solde (dashboard collab) ---
(async function loadMyBalance() {
  try {
    // helpers.js ajoute déjà Authorization: Bearer <token> pour API_BASEURL
    const res = await $.get(`${API_BASEURL}/collabs/me/balance`);
    if (res?.ok) {
      const amount = Number(res.balance || 0);
      // On force l'affichage en FCFA uniquement
      $('#myBalanceAmount').text(amount.toLocaleString('fr-FR') + ' FCFA');
      $('#myBalanceCurrency').text(''); // on vide ce span ou le laisser vide
    } else {
      console.warn('Balance API response not ok:', res);
    }
  } catch (e) {
    console.error('Erreur loadMyBalance:', e);
  }
})();

// Si ton layout appelle window.render, on s’y accroche.
// Sinon on fait un fallback au DOM ready.
if (typeof window.render === 'function') {
  const _prevRender = window.render;
  window.render = async function () {
    try { await _prevRender(); } catch(e) { /* ignore */ }
    refreshMyBalance();
  };
} else {
  $(refreshMyBalance);
}
