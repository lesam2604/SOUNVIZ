@extends('layouts.app')

@section('pageTitle', 'Retraits')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Retraits de commissions</h4>
      <button class="btn btn-primary ms-auto" id="withdraw" data-permission="add withdrawal">
        <i class="fas fa-plus"></i> Effectuer un nouveau retrait
      </button>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Montant</th>
            <th>Effectue le</th>
            <th>Partenaire</th>
            <th>Code Partenaire</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <div class="modal" id="modalOtp" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalOtpLabel" aria-hidden="true">
    <div class="modal-dialog modal-sms">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalOtpLabel">Confirmation</h5>
          <div class="modal-header-buttons">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="modal-body" style="overflow-x: auto;">
          <form class="row">
            <div class="col-12 mb-3 text-center">
              <h5 class="text-center">Entrer ci-dessous le code de confirmation a 6 chiffres que vous avez reçu par mail.
                Le code expirera automatiquement apres 5 minutes.</h5>
              <input type="text" class="form-control form-control-lg fw-bold text-center" id="otpCode"
                placeholder="_ _ _ _ _ _" required minlength="6" maxlength="6"><br>
              <button type="button" class="btn btn-primary btn-lg" id="confirmOtp">Confirmer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/withdrawals/list.js')
@endsection
