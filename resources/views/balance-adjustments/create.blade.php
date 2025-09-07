@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/balance-adjustments">
        <i class="fas fa-list"></i> Liste des ajustements
      </a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="partnerId" class="form-label">Partenaire</label>
          <select id="partnerId" class="form-select"></select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="amountToWithdraw" class="form-label">Montant a retirer</label>
          <input type="number" class="form-control" id="amountToWithdraw" placeholder="Montant a retirer" required
            min="0">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 mb-3">
          <label for="reason" class="form-label">Motif de l'ajustement</label>
          <textarea id="reason" rows="15" class="form-control" maxlength="5000"
            placeholder="Entrer la raison de  l'ajustement ici..."></textarea>
          <div class="invalid-feedback"></div>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/balance-adjustments/create.js')
@endsection
