@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/card-categories"><i class="fas fa-list"></i> Liste des catégories de
        cartes</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="name" class="form-label">Nom</label>
          <input type="text" class="form-control" id="name" placeholder="Nom du produit" required maxlength="191">
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="unitPrice" class="form-label">Prix unitaire</label>
          <input type="number" class="form-control" id="unitPrice" placeholder="Prix unitaire du produit" required>
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="stockQuantityMin" class="form-label">Quantité minimum</label>
          <input type="number" class="form-control" id="stockQuantityMin" placeholder="Quantité minimum du produit"
            required>
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="picture" class="form-label">Image de carte</label>
          <input type="file" class="form-control" id="picture" required accept="image/*">
          <div class="invalid-feedback"></div>
          <div class="form-text update-image-helper">
            Si vous ignorez ce champs, l'ancienne image sera maintenue
          </div>
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
  @vite('resources/js/card-categories/create.js')
@endsection
