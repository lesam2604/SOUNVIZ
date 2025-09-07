@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/cards"><i class="fas fa-list"></i> Liste des cartes reconnues</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="cardCategoryId" class="form-label">Catégorie</label>
          <select id="cardCategoryId" class="form-select"></select>
          <div class="invalid-feedback"></div>
        </div>

        @include('partials.card-adding-types')

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite(['resources/js/partials/card-adding-types.js', 'resources/js/cards/create.js'])
@endsection
