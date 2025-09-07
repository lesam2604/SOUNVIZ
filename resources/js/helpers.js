window.API_BASEURL = $('meta[name="api_baseurl"]').attr('content');
window.TOAST_DURATION = 7000;

// ✅ Correctif CSRF pour routes web
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
if (csrf) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrf
    }
  });
}

window.USER = {
  set: function (user) {
    for (const key in user) {
      this[key] = user[key];
    }
  },
  hasRole: function (...roles) {
    for (const role of roles) {
      if (this.roles.indexOf(role) !== -1) {
        return true;
      }
    }
    return false;
  },
  can: function (...permissions) {
    for (const permission of permissions) {
      if (this.permissions.indexOf(permission) !== -1) {
        return true;
      }
    }
    return false;
  },
  cannot: function (...permissions) {
    return !this.can(...permissions);
  },
};
window.SETTINGS = null;

window.isValidURL = function (url) {
  try {
    const newUrl = new URL(url);
    return newUrl.protocol === 'http:' || newUrl.protocol === 'https:';
  } catch (err) {
    return false;
  }
};

window.Toast = Swal.mixin({
  toast: true,
  position: 'top',
  showConfirmButton: false,
  timer: TOAST_DURATION,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  },
});

window.toast = function (
  text = 'SZ',
  duration = TOAST_DURATION,
  color = 'info',
  placement = 'top-center',
  close = true
) {
  const [gravity, position] = placement.split('-');
  const background = {
    info: '#435ebe',
    success: '#4fbe87',
    error: '#ef5350',
  }[color];

  Toastify({
    text,
    duration,
    close,
    gravity,
    position,
    style: { background },
  }).showToast();
};

window.toastInfo = function (text) {
  toast(text, TOAST_DURATION, 'info', 'top-center', true);
};

window.toastSuccess = function (text) {
  toast(text, TOAST_DURATION, 'success', 'top-center', true);
};

window.toastError = function (text) {
  toast(text, TOAST_DURATION, 'error', 'top-center', true);
};

// ✅ Setup API avec Bearer token
/*$.ajaxSetup({
  headers: {
    Authorization: `Bearer ${localStorage.getItem('token')}`,
  },
});*/





$.ajaxSetup({
  beforeSend: function (xhr, settings) {
    const url = settings?.url || '';
    const isApi = typeof API_BASEURL === 'string' && url.startsWith(API_BASEURL);
    // Bearer uniquement pour l'API (auth:sanctum)
    if (isApi) {
      const token = localStorage.getItem('token') || '';
      if (token) xhr.setRequestHeader('Authorization', `Bearer ${token}`);
    }
    // CSRF pour les routes web
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    if (csrf) xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  }
});





window.hidePreloader = function () {
  $('#preloader-container').hide();
  $('body').removeClass('loading');
};

window.ajax = function (config) {
  return new Promise((resolve, reject) => {
    config.success = (data, textStatus, jqXHR) => {
      resolve({ data, textStatus, jqXHR });
    };

    config.error = (error, textStatus, errorThrown) => {
      reject({ error, textStatus, errorThrown });
    };

    if ('dataType' in config === false) {
      config.dataType = 'json';
    }

    try {
      // Injecter les champs de commissions manuelles pour les créations/mises à jour d'opérations
      const url = (config?.url || '').toString();
      const isOperationEndpoint = url.includes('/operations/') && (url.includes('/store') || url.includes('/update/'));
      if (isOperationEndpoint && config?.data instanceof FormData) {
        const manualFee = $('#manual_fee').val();
        const manualPlat = $('#manual_platform_commission').val();
        if (typeof manualFee !== 'undefined') {
          config.data.append('manual_fee', manualFee ?? '');
        }
        if (typeof manualPlat !== 'undefined') {
          config.data.append('manual_platform_commission', manualPlat ?? '');
        }
      }
    } catch (e) { /* noop */ }

    $.ajax(config);
  });
};

window.downloadFile = async function (
  paramUrl,
  paramType = 'GET',
  paramData = {}
) {
  let { data, textStatus, jqXHR } = await ajax({
    url: paramUrl,
    type: paramType,
    data: paramData,
    dataType: null,
    xhr: function () {
      let xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState == 2) {
          if (xhr.status == 200) {
            xhr.responseType = 'blob';
          } else {
            xhr.responseType = 'text';
          }
        }
      };
      return xhr;
    },
  });

  let fileName = '';
  let disposition = jqXHR.getResponseHeader('Content-Disposition');

  if (disposition && disposition.indexOf('attachment') !== -1) {
    let fileNameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
    let matches = fileNameRegex.exec(disposition);
    if (matches != null && matches[1]) {
      fileName = matches[1].replace(/['"]/g, '');
    }
  }

  let url = URL.createObjectURL(data);
  let a = $(`<a href="${url}" download="${fileName}"></a>`).appendTo('body');
  a[0].click();
  a.remove();
  URL.revokeObjectURL(url);
};

window.getUser = async function () {
  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/user`,
      type: 'GET',
    });

    return data;
  } catch ({ error }) {
    throw new Error(error.responseJSON.message);
  }
};

window.getSettings = async function () {
  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/settings`,
      type: 'GET',
    });

    return data;
  } catch ({ error }) {
    throw new Error(error.responseJSON.message);
  }
};

window.logout = async function () {
  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/logout`,
      type: 'POST',
    });

    return data;
  } catch ({ error }) {
    throw new Error(error.responseJSON.message);
  }
};

window.getUploadUrl = function (image) {
  return isValidURL(image)
    ? image
    : API_BASEURL + '/public-files/uploads/' + image;
};

window.getThumbnailUrl = function (image) {
  return isValidURL(image)
    ? image
    : API_BASEURL + '/public-files/thumbnails/' + image;
};

window.getCountryFlagUrl = function (countryCode, width = 32, height = 24) {
  return `https://flagcdn.com/${width}x${height}/${countryCode}.png`;
};

window.swalLoading = function () {
  Swal.fire({
    title: 'Traitement en cours...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });
};

window.setCookie = function (cookieName, cookieValue) {
  document.cookie = `${cookieName}=${cookieValue}; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/;`;
};

window.deleteCookie = function (cookieName) {
  document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;`;
};

window.getTimeAgo = function (date) {
  const now = moment();
  const then = moment(date);

  const diffSeconds = now.diff(then, 'seconds');
  const diffMinutes = now.diff(then, 'minutes');
  const diffHours = now.diff(then, 'hours');

  if (diffSeconds < 60) {
    return `Il y a ${diffSeconds} seconde${diffSeconds > 1 ? 's' : ''}`;
  } else if (diffMinutes < 60) {
    return `Il y a ${diffMinutes} minute${diffMinutes > 1 ? 's' : ''}`;
  } else if (diffHours < 24) {
    return `Il y a ${diffHours} heure${diffHours > 1 ? 's' : ''}`;
  }

  return then.format('YYYY-MM-DD HH:mm:ss');
};

window.toCamelCase = function (str) {
  return str.replace(/([-_][a-z])/g, (match) => {
    return match.charAt(1).toUpperCase();
  });
};

window.showErrors = function (errors, selectors = null) {
  if (errors.errors) {
    $('.is-invalid').removeClass('is-invalid');

    for (const key in errors.errors) {
      let selector = selectors && selectors[key] ? selectors[key] : '#' + key;
      $(selector)
        .addClass('is-invalid')
        .siblings('.invalid-feedback')
        .text(errors.errors[key][0]);
    }
  } else if (errors.message) {
    Swal.fire(errors.message, '', 'error');
  }
};

window.viewErrors = function (errors) {
  let array = [];

  for (const key in errors) {
    errors[key].forEach((err) => array.push(err));
  }

  Swal.fire({
    title: 'Erreurs',
    html: array.join('<br>'),
    icon: 'error',
  });
};

window.firstError = function (errors) {
  for (const key in errors) {
    return errors[key][0];
  }
};

window.setTitle = function (title) {
  $('#title').html(title);
  document.title = `${title} | SOUNVI ZEHOUE`;
};

window.formatAmount = function (amount) {
  return parseInt(amount) + ' FCFA';
};

window.formatAmountSpaced = function (amount) {
  return parseInt(amount).toLocaleString('fr-FR') + ' FCFA';
};

window.populatePartners = function (selector, fixedCode = false) {
  const templatePartnerResult = (partner) => {
    if (!partner.id) {
      return partner.text;
    }

    return $(`
      <div class="d-flex align-items-center position-relative mb-3">
        <div class="avatar avatar-2xl">
          <img class="rounded-circle" src="${getThumbnailUrl(
            partner.picture
          )}" alt="">
        </div>
        <div class="flex-1 ms-3">
          <h6 class="mb-0 fw-semi-bold">${partner.first_name} ${
      partner.last_name
    } (${partner.code})</h6>
          <p class="text-500 fs--2 mb-0">${partner.company_name}</p>
        </div>
      </div>
    `);
  };

  const templatePartnerSelection = (partner) => {
    if (!partner.id) {
      return partner.text;
    }

    return $(
      `<span>${partner.first_name} ${partner.last_name} (${partner.code})</span>`
    );
  };

  $(selector).each(function () {
    $(this).select2({
      data: [],
      theme: 'bootstrap-5',
      placeholder: 'Partenaire',
      allowClear: true,
      templateResult: templatePartnerResult,
      templateSelection: templatePartnerSelection,
      ajax: {
        url: `${API_BASEURL}/partners/fetch-by-term`,
        data: function (params) {
          return {
            term: params.term,
            fixed_code: fixedCode,
          };
        },
        processResults: function (data) {
          return { results: data };
        },
      },
    });
  });
};

window.populateCompanies = function (selector) {
  const templateResult = (company) => {
    if (!company.id) {
      return company.text;
    }

    return $(`
      <div class="d-flex align-items-center position-relative mb-3">
        <div class="flex-1 ms-3">
          <h6 class="mb-0 fw-semi-bold">${company.name}</h6>
          <p class="text-500 fs--2 mb-0">${company.tin}</p>
        </div>
      </div>
    `);
  };

  const templateSelection = (company) => {
    if (!company.id) {
      return company.text;
    }

    return $(`<span>${company.name} (${company.tin})</span>`);
  };

  $(selector).each(function () {
    $(this).select2({
      data: [],
      theme: 'bootstrap-5',
      placeholder: `Nom d’établissement, numéro IFU`,
      allowClear: true,
      templateResult,
      templateSelection,
      ajax: {
        url: `${API_BASEURL}/partners/fetch-companies-by-term`,
        data: function (params) {
          return {
            term: params.term,
          };
        },
        processResults: function (data) {
          return { results: data };
        },
      },
    });
  });
};

window.htmlEntities = function (str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

window.formatDate = function (str) {
  return moment(str).format('YYYY-MM-DD');
};

window.formatDateTime = function (str) {
  return moment(str).format('YYYY-MM-DD HH:mm:ss');
};

window.truncate = function (str, maxLength = 20) {
  if (str.length <= maxLength) {
    return str;
  }
  return str.substring(0, maxLength) + '...';
};

window.renderScrollingMessageHtml = function (message) {
  const sizes = {
    small: 'h5',
    medium: 'h3',
    large: 'h1',
  };

  const colors = {
    black: 'black',
    blue: 'blue',
    red: 'red',
    yellow: 'yellow',
    green: 'green',
  };

  return $(`<${sizes[message.size]}/>`)
    .addClass('moving-text text-nowrap')
    .css('color', colors[message.color])
    .css('animation-duration', `${message.time}s`)
    .text(message.content);
};

window.getUbaTypes = async function () {
  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/operations/uba-types`,
      type: 'GET',
    });

    return data;
  } catch ({ error }) {
    throw new Error(error.responseJSON.message);
  }
};

window.getResource = async function (url, type = 'GET', data = {}) {
  try {
    let { data: result } = await ajax({ url, type, data });
    return [result, null];
  } catch ({ error }) {
    console.log(error);
    return [null, error];
  }
};

window.ubaTypes = null;
window.extraClients = null;

window.populateUbaTypes = async function (
  selector,
  dropdownParent = null,
  defaultValue = ''
) {
  if (window.ubaTypes === null) {
    window.ubaTypes = await getUbaTypes();
  }

  $(selector).each(function () {
    $(this).html(`<option value=""></option>`);

    for (const ubaType of window.ubaTypes) {
      $(this).append(`<option value="${ubaType}">${ubaType}</option>`);
    }

    $(this)
      .select2({
        placeholder: 'Toutes les catégories de cartes',
        allowClear: true,
        theme: 'bootstrap-5',
        dropdownParent,
      })
      .val(defaultValue)
      .change();
  });
};

window.populateExtraClients = async function (
  selector,
  dropdownParent = null,
  defaultValue = ''
) {
  if (window.extraClients === null) {
    window.extraClients = (
      await getResource(`${API_BASEURL}/extra-clients/fetch-all`)
    )[0];
  }

  $(selector).each(function () {
    $(this).html(`<option value=""></option>`);

    for (const extraClient of window.extraClients) {
      $(this).append(`
        <option value="${extraClient.id}">
          ${extraClient.first_name} ${extraClient.last_name} (${extraClient.company_name} - ${extraClient.tin})
        </option>
      `);
    }

    $(this)
      .select2({
        placeholder: 'Sélectionnez un client extra',
        allowClear: true,
        theme: 'bootstrap-5',
        dropdownParent,
      })
      .val(defaultValue)
      .change();
  });
};

window.hasCommissions = function (user, opTypeId, cardType) {
  cardType ||= null;

  return user.operation_types.find(
    (opt) =>
      opt.operation_type_id === opTypeId &&
      opt.card_type === cardType &&
      opt.has_commissions
  );
};

window.cardTypes = null;

window.getCardTypes = async function () {
  try {
    let { data } = await ajax({
      url: `${API_BASEURL}/card-types/fetch-all`,
      type: 'GET',
    });

    return data;
  } catch ({ error }) {
    throw new Error(error.responseJSON.message);
  }
};

window.populateCardTypes = async function (
  selector,
  dropdownParent = null,
  defaultValue = ''
) {
  if (window.cardTypes === null) {
    window.cardTypes = await getCardTypes();
  }

  $(selector).each(function () {
    $(this).html(`<option value=""></option>`);

    for (const cardType of window.cardTypes) {
      $(this).append(`<option value="${cardType}">${cardType}</option>`);
    }

    $(this)
      .select2({
        placeholder: 'Sélectionnez un type de carte',
        allowClear: true,
        theme: 'bootstrap-5',
        dropdownParent,
      })
      .val(defaultValue)
      .change();
  });
};
