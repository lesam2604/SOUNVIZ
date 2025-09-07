@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-supplies"><i class="fas fa-list"></i> Liste des
        approvisionnements</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Produit</th>
            <td id="product"></td>
          </tr>
          <tr>
            <th>Catégorie</th>
            <td id="category"></td>
          </tr>
          <tr>
            <th>Quantité approvisionnée</th>
            <td id="quantity"></td>
          </tr>
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
  @vite('resources/js/inv-supplies/view.js')
@endsection
