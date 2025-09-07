@extends('layouts.app')

@section('pageTitle', 'Produits')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-products/create"><i class="fas fa-plus"></i> Ajouter un nouveau
        produit</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Nom</th>
            <th>Prix Unitaire</th>
            <th>Catégorie</th>
            <th>Quantité en stock</th>
            <th>Quantité minimum</th>
            <th>Ajouté le</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $toSupply ?? '' }}" id="toSupply">
@endsection

@section('pageJs')
  @vite('resources/js/inv-products/list.js')
@endsection
