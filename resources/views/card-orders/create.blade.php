@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/card-orders"><i class="fas fa-list"></i> Liste des commandes de
        cartes</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="clientType" class="form-label">Type Client</label>
          <select id="clientType" class="form-select">
            <option value="partner">Partenaire</option>
            <option value="extra_client">Client Extra</option>
          </select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="partnerId" class="form-label">Partenaire</label>
          <select id="partnerId" class="form-select"></select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="extraClientId" class="form-label">Client Extra</label>
          <select id="extraClientId" class="form-select"></select>
          <div class="invalid-feedback"></div>
        </div>

        @include('partials.card-adding-types')

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite(['resources/js/partials/card-adding-types.js', 'resources/js/card-orders/create.js'])
@endsection
