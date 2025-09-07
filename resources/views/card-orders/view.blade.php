@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/card-orders"><i class="fas fa-list"></i> Liste des commandes de
        cartes</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Code Client</th>
            <td id="codeClient"></td>
          </tr>
          <tr>
            <th>Nom Client</th>
            <td id="lastName"></td>
          </tr>
          <tr>
            <th>Prénom Client</th>
            <td id="firstName"></td>
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
            <th>Numéro de téléphone</th>
            <td id="phoneNumber"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="titleCards"></h4>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered" id="tableCards">
        <thead>
          <tr>
            <th>#</th>
            <th>Id</th>
            <th>Catégorie</th>
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
      <button type="button" class="btn btn-lg btn-outline-primary" id="generateBill" data-permission="generate-bill card_order">
        <i class="fas me-2 fa-file-pdf"></i> Générer la facture
      </button>
      <button type="button" class="btn btn-lg btn-danger" id="delete" data-permission="delete card_order"><i
          class="fas me-2 fa-trash"></i> Supprimer</button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/card-orders/view.js')
@endsection
