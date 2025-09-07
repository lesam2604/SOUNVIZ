@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/partners">
        <i class="fas fa-list"></i> Liste des partenaires
      </a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
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

        <div class="col-12 col-lg-6 mb-3">
          <label for="phoneNumber" class="form-label">Numéro de telephone</label>
          <input type="text" class="form-control" id="phoneNumber" placeholder="Numéro de telephone" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" placeholder="Email" required maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="confirmEmail" class="form-label">Confirmer Email</label>
          <input type="email" class="form-control" id="confirmEmail" placeholder="Email" required maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="idCardNumber" class="form-label">Numéro de la carte d’identité</label>
          <input type="text" class="form-control" id="idCardNumber" placeholder="Numéro de la carte d’identité"
            required maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="address" class="form-label">Adresse</label>
          <input type="text" class="form-control" id="address" placeholder="Adresse" required maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="companyName" class="form-label">Nom d’établissement</label>
          <input type="text" class="form-control" placeholder="Votre nom d’établissement" id="companyName">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="tin" class="form-label">Numéro IFU</label>
          <input type="text" class="form-control" placeholder="Votre numéro IFU" id="tin">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="picture" class="form-label">Photo de profile</label>
          <input type="file" class="form-control" id="picture" required accept="image/*">
          <div class="invalid-feedback"></div>
          <div class="form-text update-image-helper">
            Si vous ignorez ce champs, l'ancienne image sera maintenue
          </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="idCardPicture" class="form-label">Image de la carte d’identité</label>
          <input type="file" class="form-control" id="idCardPicture" required accept="image/*">
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
  @vite('resources/js/partners/edit.js')
@endsection
