@extends('layouts.app')

@section('pageContent')
<div class="card mb-3">
  <div class="card-header d-flex align-items-center">
    <h4 class="mb-0" id="title">Nouvelle facture</h4>
    <a class="btn btn-outline-primary ms-auto" href="/invoices"><i class="fas fa-list"></i> Liste des factures</a>
  </div>
  <div class="card-body">
    <form id="form" class="row" novalidate>
      <div class="col-12 col-lg-6 mb-3">
        <label for="opTypeCode" class="form-label">Type d'opération</label>
        <select id="opTypeCode" class="form-select"></select>
        <div class="form-text">Sélectionnez le type d'opération concerné par la facture.</div>
      </div>

      <div class="col-12 col-lg-6 mb-3">
        <label for="clientType" class="form-label">Type de client</label>
        <select id="clientType" class="form-select">
          <option value="partner" selected>Partenaire</option>
          <option value="extra_client">Client extra</option>
          <option value="external">Client (manuel)</option>
        </select>
      </div>

      <div class="col-12 col-lg-6 mb-3" id="partnerSelectBlock">
        <label for="partnerId" class="form-label">Partenaire</label>
        <select id="partnerId" class="form-select" style="width: 100%"></select>
      </div>

      <div class="col-12 col-lg-6 mb-3" id="extraClientSelectBlock" style="display:none;">
        <label for="extraClientId" class="form-label">Client extra</label>
        <select id="extraClientId" class="form-select" style="width: 100%"></select>
        <div class="form-text">Sélectionnez un client extra existant.</div>
      </div>

      <div id="manualClientBlock" class="col-12">
        <div class="alert alert-info">Client externe: renseignez les informations manuellement.</div>
        <div class="row">
          <div class="col-12 col-lg-6 mb-3">
            <label for="client_name" class="form-label">Nom du client</label>
            <input type="text" class="form-control" id="client_name" placeholder="Nom du client">
          </div>
          <div class="col-12 col-lg-6 mb-3">
            <label for="client_phone" class="form-label">Téléphone</label>
            <input type="tel" class="form-control" id="client_phone" placeholder="Ex: 97000000">
          </div>
          <div class="col-12 col-lg-6 mb-3">
            <label for="client_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="client_email" placeholder="client@example.com">
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6 mb-3">
        <label for="amount" class="form-label">Montant (FCFA)</label>
        <input type="number" min="0" class="form-control" id="amount" placeholder="0">
        <div class="form-text">Vous pouvez aussi laisser vide et utiliser des lignes d'articles ci-dessous.</div>
      </div>

      <div class="col-12">
        <div class="d-flex align-items-center mb-2">
          <h5 class="mb-0">Lignes</h5>
          <button type="button" id="addItemBtn" class="btn btn-sm btn-outline-primary ms-auto"><i class="fas fa-plus"></i> Ajouter</button>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered" id="itemsTable">
            <thead>
              <tr>
                <th>Désignation</th>
                <th style="width:120px;">Qté</th>
                <th style="width:160px;">PU</th>
                <th style="width:40px;"></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="text-center mt-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
      </div>
    </form>
  </div>
</div>
<div class="card mt-3" id="extraClientDetails" style="display:none;">
  <div class="card-header d-flex align-items-center">
    <h5 class="mb-0">Détails client extra</h5>
  </div>
  <div class="card-body">
    <table class="table table-bordered">
      <tbody>
        <tr>
          <th>Entreprise</th>
          <td id="ecCompany"></td>
        </tr>
        <tr>
          <th>IFU</th>
          <td id="ecTin"></td>
        </tr>
        <tr>
          <th>Nom</th>
          <td id="ecFullName"></td>
        </tr>
        <tr>
          <th>Téléphone</th>
          <td id="ecPhone"></td>
        </tr>
        <tr>
          <th>Email</th>
          <td id="ecEmail"></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('pageJs')
  @vite('resources/js/invoices/create.js')
@endsection
