@extends('layouts.app')

@section('pageTitle', 'Commandes')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Commandes de produits</h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-orders/create"><i class="fas fa-plus"></i> Effectuer une nouvelle
        commande</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Ajoute le</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/inv-orders/list.js')
@endsection
