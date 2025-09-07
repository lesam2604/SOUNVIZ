@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="" id="linkList"></a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="clientType" class="form-label">Type de client</label>
          <select id="clientType" class="form-select">
            <option value="partner" selected>Partenaire</option>
            <option value="extra_client">Client (manuel)</option>
          </select>
        </div>
        <div class="col-12 col-lg-6 mb-3" id="partnerSelectBlock">
          <label for="partnerId" class="form-label">Partenaire (optionnel)</label>
          <select id="partnerId" class="form-select" style="width: 100%"></select>
          <div class="form-text">Laissez vide pour créer pour un client manuel.</div>
        </div>
        <div id="manualClientBlock" class="col-12 mt-2" style="display:none;">
          <div class="alert alert-info">
            Aucun partenaire sélectionné. Vous pouvez renseigner les informations du client.
          </div>
          <div class="row">
            <div class="col-12 col-lg-6 mb-3">
              <label for="client_full_name" class="form-label">Nom complet du client</label>
              <input type="text" class="form-control" id="client_full_name" placeholder="Nom complet">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12 col-lg-6 mb-3">
              <label for="client_phone" class="form-label">Téléphone du client</label>
              <input type="tel" class="form-control" id="client_phone" placeholder="Ex: 97000000">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12 col-lg-6 mb-3">
              <label for="client_email" class="form-label">Email du client (optionnel)</label>
              <input type="email" class="form-control" id="client_email" placeholder="client@example.com">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12 col-lg-6 mb-3">
              <label for="requester_name" class="form-label">Nom du demandeur (collaborateur/admin)</label>
              <input type="text" class="form-control" id="requester_name" placeholder="Votre nom">
              <div class="invalid-feedback"></div>
            </div>
          </div>
        </div>
        <div class="text-center" id="blockSubmit">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>
  </div>

  <div class="card" id="blockCommissions">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Commissions</h4>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
          <label for="manual_fee" class="form-label">Frais de course (manuel)</label>
          <input type="number" inputmode="decimal" class="form-control" id="manual_fee" placeholder="Laissez vide pour automatique">
          <div class="form-text">Si renseigné, ce montant sera utilisé comme frais de course.</div>
        </div>
        <div class="col-12 col-lg-6">
          <label for="manual_platform_commission" class="form-label">Commission de la plateforme (manuel)</label>
          <input type="number" inputmode="decimal" class="form-control" id="manual_platform_commission" placeholder="Laissez vide pour 0">
          <div class="form-text">Si renseigné, la commission partenaire sera calculée comme (frais - commission plateforme).</div>
        </div>
      </div>
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Montant de l'opération</th>
            <td id="opAmount" class="fw-bold"></td>
          </tr>
          <tr>
            <th>Frais de course</th>
            <td id="opFee" class="fw-bold"></td>
          </tr>
          <tr>
            <th>Montant total a payer</th>
            <td id="opTotalAmount" class="fw-bold text-danger"></td>
          </tr>
          <tr class="d-none">
            <th>Votre solde</th>
            <td id="opCurrentBalance" class="fw-bold text-success"></td>
          </tr>
          <tr class="d-none">
            <th>Solde requis</th>
            <td id="opRequired" class="fw-bold"></td>
          </tr>
          
          <tr>
            <th>Commission</th>
            <td id="opCommission" class="fw-bold"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $opTypeCode }}" id="opTypeCode">
  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/operations/create.js')
  <script>
    (function() {
      $(function() {
        // Redéfinir updateFeeAndCommission pour prendre en compte la commission plateforme manuelle
        const originalUpdate = window.updateFeeAndCommission;
        window.updateFeeAndCommission = function(amount = null) {
          try {
            const parseIntSafe = (v) => {
              if (v === null || v === undefined) return 0;
              const s = ('' + v).replace(/\s/g, '').replace(/,/g, '.');
              const n = parseFloat(s);
              return isNaN(n) ? 0 : Math.round(n);
            };

            const opTypeCode = $('#opTypeCode').val();
            const opType = (window.SETTINGS?.opTypes || []).find(function(ot){ return ot.code === opTypeCode; });
            if (!opType) { if (typeof originalUpdate === 'function') return originalUpdate(amount); return; }

            const inputAmt = parseIntSafe(amount ?? (opType.amount_field ? ($('#' + opType.amount_field).val() || 0) : 0));
            const effFee = parseIntSafe($('#manual_fee').val() || 0);
            const effPlat = parseIntSafe($('#manual_platform_commission').val() || 0);

            // Si au moins un des champs manuels est renseigné, on force le nouveau calcul
            if ($('#manual_fee').val() !== '' || $('#manual_platform_commission').val() !== '') {
              const totalDebited = inputAmt + effFee + effPlat;
              $('#opAmount').html(formatAmount(inputAmt));
              $('#opFee').html(formatAmount(effFee));
              $('#opCommission').html(formatAmount(0));
              $('#opTotalAmount').html(formatAmount(totalDebited));
              $('#opRequired').html(formatAmount(totalDebited));
              return;
            }

            // Sinon, fallback sur le comportement existant
            if (typeof originalUpdate === 'function') originalUpdate(amount);
            // Si c'est un collaborateur, considérer la commission partenaire comme 0
            if (window.USER?.hasRole && window.USER.hasRole('collab')) {
              $('#opCommission').html(formatAmount(0));
            }
          } catch (e) {
            try { if (typeof originalUpdate === 'function') return originalUpdate(amount); } catch (_) {}
          }
        };
        // Remplacer le submit handler si inactif
        $('#form').off('submit').on('submit', async function(e) {
          e.preventDefault();
          try {
            const opTypeCode = $('#opTypeCode').val();
            const opType = (window.SETTINGS?.opTypes || []).find(function(ot){ return ot.code === opTypeCode; });
            if (!opType) {
              return Toast.fire("Type d'opération introuvable", '', 'error');
            }

            const formData = new FormData();

            // Champs stockés
            for (const pair of opType.sorted_fields) {
              const fieldName = pair[0];
              const fieldData = pair[1];
              if (!fieldData.stored) continue;

              switch (fieldData.type) {
                case 'select':
                case 'text':
                case 'textarea':
                case 'email':
                case 'country':
                case 'date':
                case 'datetime':
                case 'number':
                  formData.append(fieldName, ($('#' + fieldName).val() ?? ''));
                  break;
                case 'card':
                  formData.append(fieldName, ($('#' + fieldName).val() || '').replace(/\D/g, ''));
                  break;
                case 'file':
                  formData.append(fieldName, ($('#' + fieldName)[0]?.files?.[0] ?? ''));
                  break;
              }
            }

            // Déterminer endpoint + données client manuel si besoin
            let endpoint = `${API_BASEURL}/operations/${opType.code}/store`;
            const clientType = $('#clientType').val() || 'partner';
            const selectedPartnerId = $('#partnerId').val();
            if (clientType === 'partner') {
              if (!selectedPartnerId) {
                return Toast.fire('Veuillez sélectionner un partenaire', '', 'error');
              }
              endpoint = `${API_BASEURL}/operations/${opType.code}/store-for-partner/${selectedPartnerId}`;
            } else {
              formData.append('client_full_name', $('#client_full_name').val() || '');
              formData.append('client_phone', $('#client_phone').val() || '');
              formData.append('client_email', $('#client_email').val() || '');
              formData.append('requester_name', $('#requester_name').val() || (window.USER?.full_name || ''));
              endpoint = `${API_BASEURL}/operations/${opType.code}/store-without-partner`;
            }

            // Envoi
            swalLoading();
            const { data } = await ajax({
              url: endpoint,
              type: 'POST',
              contentType: false,
              processData: false,
              data: formData,
            });
            Toast.fire(data.message || 'Opération enregistrée', '', 'success');
            // Reset simple du formulaire
            try { document.getElementById('form').reset(); } catch (e) {}
          } catch (x) {
            console.log(x);
            if (x?.error?.responseJSON) {
              const resp = x.error.responseJSON;
              if (resp.errors) { Swal.close(); }
              if (typeof window.showErrors === 'function') {
                window.showErrors(resp);
              } else {
                Swal.fire(resp.message || 'Erreur', '', 'error');
              }
            }
          }
        });
      });
    })();
  </script>
@endsection
