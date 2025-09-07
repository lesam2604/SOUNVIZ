@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-orders"><i class="fas fa-list"></i> Liste des commandes de
        produits</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="clientFirstName" class="form-label">Prénom du client</label>
          <input type="text" class="form-control" id="clientFirstName" placeholder="Prénom du client" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="clientLastName" class="form-label">Nom du client</label>
          <input type="text" class="form-control" id="clientLastName" placeholder="Nom du client" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        @include('partials.product-adding')

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite(['resources/js/partials/product-adding.js', 'resources/js/inv-orders/create.js'])
@endsection
