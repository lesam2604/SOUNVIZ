@extends('layouts.app')

@section('pageTitle', 'Changer votre mot de passe')

@section('pageContent')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Changer votre mot de passe</h4>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label class="label" for="passwordOld">Mot de passe actuel</label>
              <input type="password" id="passwordOld" maxlength="191" class="form-control">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 col-md-6 mb-3">
              <label class="label" for="passwordNew">Nouveau mot de passe</label>
              <input type="password" id="passwordNew" maxlength="191" class="form-control">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 col-md-6 mb-3">
              <label class="label" for="passwordNewConfirm">Répéter le nouveau mot de passe</label>
              <input type="password" id="passwordNewConfirm" maxlength="191" class="form-control">
              <div class="invalid-feedback"></div>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <button class="btn btn-primary" id="submit">Soumettre</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/auth/changepwd.js')
@endsection
