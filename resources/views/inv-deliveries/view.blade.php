@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-deliveries"><i class="fas fa-list"></i> Liste des livraisons de
        produits</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code de la livraison</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Code de la commande</th>
            <td id="invOrderCode"></td>
          </tr>
          <tr>
            <th>Prénom du client</th>
            <td id="clientFirstName"></td>
          </tr>
          <tr>
            <th>Nom du client</th>
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
            <th>Catégorie</th>
            <th>Prix unitaire</th>
            <th>Quantité livrée</th>
            <th>Coût total</th>
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
        Éditer</button>
      <button type="button" class="btn btn-lg btn-danger" id="delete"><i class="fas me-2 fa-trash"></i>
        Supprimer</button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/inv-deliveries/view.js')
@endsection
