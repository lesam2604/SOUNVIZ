@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
<meta name="csrf-token" content="{{ csrf_token() }}">


<div class="card mb-3">
  <div class="card-header d-flex align-items-center">
    <h4 class="mb-0" id="title"></h4>
    <a class="btn btn-outline-primary ms-auto" href="/collabs"><i class="fas fa-list"></i> Liste des collaborateurs</a>
  </div>
  <div class="card-body" style="overflow-x: auto;">
    <table class="table table-bordered">
      <tbody>
        <tr>
          <th>Code</th>
          <td id="code"></td>
        </tr>
        <tr>
          <th>Prénom</th>
          <td id="firstName"></td>
        </tr>
        <tr>
          <th>Nom</th>
          <td id="lastName"></td>
        </tr>
        <tr>
          <th>Numéro de téléphone</th>
          <td id="phoneNumber"></td>
        </tr>
        <tr>
          <th>Email</th>
          <td id="email"></td>
        </tr>
        <tr>
          <th>Photo</th>
          <td id="picture"></td>
        </tr>
        <tr>
          <th>Status</th>
          <td id="status"></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex align-items-center">
    <h4 class="mb-0">Solde du collaborateur</h4>
    <button type="button" class="btn btn-outline-primary ms-auto" id="btnOpenAdjust">
      <i class="fas fa-coins me-1"></i> Ajuster le solde
    </button>
  </div>
  <div class="card-body">
    <div>Solde actuel : <span id="collabBalance" class="fw-bold">—</span> <span id="collabCurrency" class="fw-bold"></span></div>
  </div>
</div>

<input type="hidden" id="objectId" value="{{ $objectId ?? '' }}">

<div class="card mb-3">
  <div class="card-header d-flex align-items-center">
    <h4 class="mb-0">Actions</h4>
  </div>
  <div class="card-body">
    <button type="button" class="btn btn-lg btn-outline-success" id="enable"><i class="fas me-2 fa-check"></i> Activer</button>
    <button type="button" class="btn btn-lg btn-outline-danger" id="disable"><i class="fas me-2 fa-ban"></i> Désactiver</button>
    <button type="button" class="btn btn-lg btn-danger" id="delete"><i class="fas me-2 fa-trash"></i> Supprimer</button>
  </div>
</div>

<!-- Modal Ajustement du solde -->
<div class="modal fade" id="modalAdjustBalance" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formAdjustBalance">
      <div class="modal-header">
        <h5 class="modal-title">Ajuster le solde du collaborateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Type d’opération</label>
          <select class="form-select" name="direction" required>
            <option value="credit">Créditer</option>
            <option value="debit">Débiter</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Montant</label>
          <input type="number" class="form-control" name="amount" min="1" step="1" placeholder="Ex: 10000" required>
          <div class="form-text">Saisis l’unité que tu as choisie de stocker (ex: XOF, pas de virgule).</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Motif</label>
          <textarea class="form-control" name="reason" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Confirmer</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('pageJs')
@vite('resources/js/collabs/view.js')
@endsection
