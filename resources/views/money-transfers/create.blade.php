@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/money-transfers"><i class="fas fa-list"></i> Liste des transferts</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="recipientId" class="form-label">Récepteur</label>
          <select name="" id="recipientId" class="form-select"></select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="amount" class="form-label">Montant</label>
          <input type="number" class="form-control" id="amount" placeholder="Montant" min="1" required>
          <div class="invalid-feedback"></div>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/money-transfers/create.js')
@endsection
