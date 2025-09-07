@extends('layouts.auth')

@section('pageTitle', 'Connexion')

@section('pageContent')
  <form class="mt-4" id="form" method="POST" action="/login" novalidate>
    @csrf

    <div class="input-group has-validation uf-input-group input-group-lg mb-3">
      <span class="input-group-text fa fa-user"></span>
      <input
        type="email"
        class="form-control @error('email') is-invalid @enderror"
        placeholder="Entrez votre adresse email"
        id="email"
        name="email"
        value="{{ old('email') }}"
        required
      >
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @else
        <div class="invalid-feedback"></div>
      @enderror
    </div>

    <div class="input-group has-validation uf-input-group input-group-lg mb-3">
      <span class="input-group-text fa fa-lock"></span>
      <input
        type="password"
        class="form-control @error('password') is-invalid @enderror"
        placeholder="Entrez votre mot de passe"
        id="password"
        name="password"
        required
      >
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @else
        <div class="invalid-feedback"></div>
      @enderror
    </div>

    <div class="d-flex mb-3 justify-content-between">
      <div class="form-check">
        <input type="checkbox" class="form-check-input uf-form-check-input" id="remember" name="remember">
        <label class="form-check-label text-white" for="remember">Se souvenir</label>
      </div>
      <a href="/passwordresetemail">Mot de passe oublié?</a>
    </div>

    <div class="d-grid mb-4">
      <button type="submit" class="btn uf-btn-primary btn-lg">Se connecter</button>
    </div>

    <div class="mt-4 text-center">
      <span class="text-white">Vous êtes nouveau?</span>
      <a href="/partners/register">Inscrivez-vous</a>
    </div>
  </form>
@endsection

@section('pageJs')
  @vite('resources/js/auth/login.js')
@endsection
