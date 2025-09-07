@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/card-categories/create"><i class="fas fa-plus"></i> Ajouter une
        nouvelle catégorie de cartes</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Nom</th>
            <th>Prix Unitaire</th>
            <th>Quantité en stock</th>
            <th>Quantité minimum</th>
            <th>Cree le</th>
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
  @vite('resources/js/card-categories/list.js')
@endsection
