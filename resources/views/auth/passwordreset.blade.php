@extends('layouts.auth')

@section('pageTitle', 'Réinitialisation')

@section('pageContent')
  <form class="mt-4" id="form" novalidate>
    <div class="input-group has-validation uf-input-group input-group-lg mb-3">
      <span class="input-group-text fa fa-lock"></span>
      <input type="password" class="form-control" placeholder="Entrez votre nouveau mot de passe" id="password">
      <div class="invalid-feedback"></div>
    </div>
    <div class="input-group has-validation uf-input-group input-group-lg mb-3">
      <span class="input-group-text fa fa-lock"></span>
      <input type="password" class="form-control" placeholder="Confirmez votre nouveau mot de passe" id="confirmPassword">
      <div class="invalid-feedback"></div>
    </div>
    <div class="d-grid mb-4">
      <button type="submit" class="btn uf-btn-primary btn-lg">Réinitialiser</button>
    </div>
    <div class="mt-4 text-center">
      <span class="text-white">Vous êtes nouveau?</span>
      <a href="/partners/register">Inscrivez-vous</a>
    </div>
  </form>

  <input type="hidden" id="email" value="{{ $email }}">
  <input type="hidden" id="token" value="{{ $token }}">
@endsection

@section('pageJs')
  @vite('resources/js/auth/passwordreset.js')
@endsection
