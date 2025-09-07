@extends('layouts.app')

@section('pageTitle', 'Approvisionnements')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Approvisionnements</h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-supplies/create"><i class="fas fa-plus"></i> Ajouter un nouvel
        approvisionnement</a>
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
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('jsPlugins')
  <script src="/assets/js/pages/datatables.js"></script>
@endsection

@section('pageJs')
  @vite('resources/js/inv-supplies/list.js')
@endsection
