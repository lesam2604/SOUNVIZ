@extends('layouts.auth')

@section('pageTitle', 'Inscription')

@section('pageCss')
  <style>
    .uf-form-signin {
      max-width: 800px;
    }
  </style>
@endsection

@section('pageContent')
  <form class="mt-4 row" id="form" novalidate>
    <div class="col-12 col-lg-6 mb-3">
      <label for="lastName" class="form-label">Nom</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-user"></span>
        <input type="text" class="form-control" placeholder="Votre nom" id="lastName">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="firstName" class="form-label">Prénom</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-user"></span>
        <input type="text" class="form-control" placeholder="Votre prénom" id="firstName">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="companyName" class="form-label">Nom d’établissement</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-building"></span>
        <input type="text" class="form-control" placeholder="Votre nom d’établissement" id="companyName">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="phoneNumber" class="form-label">Numéro de telephone</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-phone-alt"></span>
        <input type="text" class="form-control" placeholder="Votre numéro de telephone" id="phoneNumber">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="email" class="form-label">Adresse email</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-at"></span>
        <input type="email" class="form-control" placeholder="Votre adresse email" id="email">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="confirmEmail" class="form-label">Confirmation de l'adresse email</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-at"></span>
        <input type="email" class="form-control" placeholder="Confirmez votre adresse email" id="confirmEmail">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="idCardNumber" class="form-label">Numéro de la carte d’identité</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-id-card"></span>
        <input type="text" class="form-control" placeholder="Votre numéro de carte d’identité" id="idCardNumber">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="tin" class="form-label">Numéro IFU</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-trademark"></span>
        <input type="text" class="form-control" placeholder="Votre numéro IFU" id="tin">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="address" class="form-label">Adresse</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-address-card"></span>
        <input type="text" class="form-control" placeholder="Votre adresse" id="address">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="picture" class="form-label">Photo de profile</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-camera"></span>
        <input type="file" class="form-control" id="picture" accept="image/*">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="idCardPicture" class="form-label">Image de la carte d’identité</label>
      <div class="input-group has-validation uf-input-group input-group-lg mb-3">
        <span class="input-group-text fa fa-image"></span>
        <input type="file" class="form-control" id="idCardPicture" accept="image/*">
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="d-grid mb-4">
      <button type="submit" class="btn uf-btn-primary btn-lg">S'inscrire</button>
    </div>
    <div class="mt-4 text-center">
      <span class="text-white">Vous avez déjà un compte?</span>
      <a href="/login">Connectez-vous</a>
    </div>
  </form>
@endsection

@section('pageJs')
  @vite('resources/js/partners/register.js')
@endsection
