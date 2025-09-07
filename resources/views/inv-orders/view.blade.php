@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-orders"><i class="fas fa-list"></i> Liste des commandes de
        produits</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>PrÃ©nom</th>
            <td id="clientFirstName"></td>
          </tr>
          <tr>
            <th>Nom</th>
            <td id="clientLastName"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="titleProducts"></h4>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered" id="tableProducts">
        <thead>
          <tr>
            <th>Code</th>
            <th>Nom</th>
            <th>CatÃ©gorie</th>
            <th>Prix unitaire</th>
            <th>QuantitÃ© commandÃ©e</th>
            <th>CoÃ»t total</th>
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
      <button type="button" class="btn btn-lg btn-outline-primary" id="edit"><i class="fas me-2 fa-pen"></i>
        Ã‰diter</button>
      <button type="button" class="btn btn-lg btn-danger" id="delete"><i class="fas me-2 fa-trash"></i>
        Supprimer</button>
      <button type="button" class="btn btn-lg btn-outline-success ms-2" id="approvePaid"><i class="fas me-2 fa-check"></i>
        Valider (payée)</button>
      <button type="button" class="btn btn-lg btn-outline-secondary ms-2" id="approveUnpaid"><i class="fas me-2 fa-check"></i>
        Valider (non payée)</button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/inv-orders/view.js')
@endsection
