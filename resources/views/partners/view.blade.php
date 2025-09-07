@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/partners"><i class="fas fa-list"></i> Liste des
        <span data-role="reviewer">partenaires</span>
        <span data-role="partner-master">boutiques</span>
      </a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Prénom</th>
            <td id="firstName"></td>
          </tr>
          <tr>
            <th>Nom</th>
            <td id="lastName"></td>
          </tr>
          <tr>
            <th>Numéro de telephone</th>
            <td id="phoneNumber"></td>
          </tr>
          <tr>
            <th>Email</th>
            <td id="email"></td>
          </tr>
          <tr>
            <th>Photo de profile</th>
            <td id="picture"></td>
          </tr>
          <tr>
            <th>Numéro de la carte d’identité</th>
            <td id="idCardNumber"></td>
          </tr>

          <tr>
            <th>Image de la carte d’identité</th>
            <td id="idCardPicture"></td>
          </tr>
          <tr>
            <th>Adresse</th>
            <td id="address"></td>
          </tr>
          <tr>
            <th>Nom d’établissement</th>
            <td id="companyName"></td>
          </tr>
          <tr>
            <th>Numéro IFU</th>
            <td id="tin"></td>
          </tr>
          <tr>
            <th>Status</th>
            <td id="status"></td>
          </tr>
          <tr>
            <th class="text-danger">Revue le</th>
            <td id="reviewedAt"></td>
          </tr>
          <tr>
            <th class="text-danger">Feedback</th>
            <td id="feedback"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card" id="opTypesCard">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="titleOpTypes"></h4>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered" id="opTypesTable">
        <thead>
          <tr>
            <th>Opération</th>
            <th>Statut des commissions</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Actions</h4>
    </div>
    <div class="card-body">
      <button type="button" class="btn btn-lg btn-outline-primary" id="edit" data-permission="edit partner">
        <i class="fas me-2 fa-pen"></i> Éditer</button>

      <button type="button" class="btn btn-lg btn-outline-success" id="approve" data-permission="review partner">
        <i class="fas me-2 fa-thumbs-up"></i> Approver
      </button>

      <button type="button" class="btn btn-lg btn-outline-danger" id="reject" data-permission="review partner">
        <i class="fas me-2 fa-thumbs-down"></i> Rejeter
      </button>

      <button type="button" class="btn btn-lg btn-outline-success" id="enable">
        <i class="fas me-2 fa-check"></i> Activer le compte
      </button>

      <button type="button" class="btn btn-lg btn-outline-danger" id="disable">
        <i class="fas me-2 fa-ban"></i> Désactiver le compte
      </button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/partners/view.js')
@endsection
