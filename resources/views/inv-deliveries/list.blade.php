@extends('layouts.app')

@section('pageTitle', 'Livraisons')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Livraisons</h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-deliveries/create"><i class="fas fa-plus"></i> Effectuer une
        nouvelle livraison</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Code Commande</th>
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
  @vite('resources/js/inv-deliveries/list.js')
@endsection
