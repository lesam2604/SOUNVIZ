@extends('layouts.auth')

@section('pageTitle', 'Récupération')

@section('pageContent')
  <form class="mt-4" id="form" novalidate>
    <div class="input-group has-validation uf-input-group input-group-lg mb-3">
      <span class="input-group-text fa fa-user"></span>
      <input type="email" class="form-control" placeholder="Entrez votre adresse email" id="email">
      <div class="invalid-feedback"></div>
    </div>
    <div class="mb-3 text-end">
      <a href="/login">Mot de passe retrouvé?</a>
    </div>
    <div class="d-grid mb-4">
      <button type="submit" class="btn uf-btn-primary btn-lg">Recevoir le lien</button>
    </div>

    <div class="mt-4 text-center">
      <span class="text-white">Vous êtes nouveau?</span>
      <a href="/partners/register">Inscrivez-vous</a>
    </div>
  </form>
@endsection

@section('pageJs')
  @vite('resources/js/auth/passwordresetemail.js')
@endsection
