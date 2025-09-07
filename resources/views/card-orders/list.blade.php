@extends('layouts.app')

@section('pageTitle', 'Commandes de cartes')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title">Commandes de cartes</h4>
      <a class="btn btn-outline-primary ms-auto" href="/card-orders/create" data-permission="add card_order">
        <i class="fas fa-plus"></i> Ajouter une nouvelle
        commande de cartes</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Nombre de cartes</th>
            <th>Client</th>
            <th>Code Client</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/card-orders/list.js')
@endsection
