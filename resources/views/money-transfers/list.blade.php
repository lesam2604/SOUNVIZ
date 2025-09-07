@extends('layouts.app')

@section('pageTitle', 'Transferts')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Transferts</h4>
      <a class="btn btn-outline-primary ms-auto" href="/money-transfers/create" data-permission="add money_transfer"><i
          class="fas fa-plus"></i> Effectuer un nouveau transfert</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Produit</th>
            <th>Catégorie</th>
            <th>Quantité</th>
            <th>Effectue le</th>
            <th>Montant</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/money-transfers/list.js')
@endsection
