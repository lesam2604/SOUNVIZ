@extends('layouts.app')

@section('pageTitle', 'Ajustement de solde')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Ajustement de solde</h4>
      <a class="btn btn-outline-primary ms-auto" href="/balance-adjustments/create" data-permission="add balance_adjustment">
        <i class="fas fa-plus"></i> Effectuer un nouvel ajustement
      </a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Partenaire</th>
            <th>Code partenaire</th>
            <th>Ancien solde</th>
            <th>Montant a retirer</th>
            <th>Nouveau solde</th>
            <th>Motif</th>
            <th>Effectue le</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/balance-adjustments/list.js')
@endsection
