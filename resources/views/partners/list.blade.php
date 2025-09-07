@extends('layouts.app')

@section('pageTitle', 'Partenaires/Boutiques')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center w-100">
        <h4 class="mb-0">
          <span data-role="reviewer">Partenaires</span>
          <span data-role="partner-master">Boutiques</span>
        </h4>
        <select class="form-select w-auto mx-2" id="status">
          <option value="">Tout</option>
          <option value="pending">En attente</option>
          <option value="enabled">Actifs</option>
          <option value="disabled">Inactifs</option>
          <option value="rejected">Rejetés</option>
        </select>
        <h5 class="ms-auto">
          Solde Total: <span id="totalBalance" class="text-danger">0</span> FCFA
        </h5>
      </div>
      <a class="btn btn-outline-primary" href="/partners/create" data-permission="add partner">
        <i class="fas fa-plus"></i> Ajouter
        <span data-role="reviewer">un nouveau partenaire</span>
        <span data-role="partner-master">une nouvelle boutique</span>
      </a>
    </div>
    <div class="card-body">
      <form id="formFilter" class="border-bottom border-top pt-3 mb-3">
        <div class="row">
          <div class="mb-3 col-lg-3 d-flex align-items-center">
            <label for="role" class="form-label me-2 mb-0">Type de compte</label>
            <select class="form-select" id="role">
              <option value="">Tout</option>
              <option value="partner-master">Principal</option>
              <option value="partner-pos">Boutique</option>
            </select>
          </div>
          <div class="mb-3 col-lg-6 d-flex align-items-center">
            <label for="companyId" class="form-label me-2 mb-0">Établissement:</label>
            <select class="form-select" id="companyId"></select>
          </div>
        </div>
      </form>
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Type de compte</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Établissement</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Solde</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $opStatus }}" id="opStatus">
@endsection

@section('pageJs')
  @vite('resources/js/partners/list.js')
@endsection
