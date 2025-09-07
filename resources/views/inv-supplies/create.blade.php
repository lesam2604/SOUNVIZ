@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-supplies"><i class="fas fa-list"></i> Liste des
        approvisionnements</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="productId" class="form-label">Produit</label>
          <select name="" id="productId" class="form-select"></select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="quantity" class="form-label">Quantité a approvisionner</label>
          <input type="number" class="form-control" id="quantity" placeholder="Quantité a approvisionner" required>
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
  @vite('resources/js/inv-supplies/create.js')
@endsection
