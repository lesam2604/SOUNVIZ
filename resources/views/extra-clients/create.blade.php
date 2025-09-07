@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/extra-clients"><i class="fas fa-list"></i> Liste des clients extra</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="companyName" class="form-label">Nom d’établissement</label>
          <input type="text" class="form-control" placeholder="Votre nom d’établissement" id="companyName" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="tin" class="form-label">Numéro IFU</label>
          <input type="text" class="form-control" placeholder="Votre numéro IFU" id="tin" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="phoneNumber" class="form-label">Numéro de téléphone</label>
          <input type="text" class="form-control" id="phoneNumber" placeholder="Numéro de telephone" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="firstName" class="form-label">Prénom</label>
          <input type="text" class="form-control" id="firstName" placeholder="Prénom" required maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="lastName" class="form-label">Nom</label>
          <input type="text" class="form-control" id="lastName" placeholder="Nom" required maxlength="191">
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
  @vite('resources/js/extra-clients/create.js')
@endsection
