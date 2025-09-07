@extends('layouts.app')

@section('pageTitle', 'Messages défilants')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Messages défilants</h4>
      <a class="btn btn-outline-primary ms-auto" href="/scrolling-messages/create"><i class="fas fa-plus"></i> Ajouter un
        nouveau message défilant</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Libelle</th>
            <th>Date de debut</th>
            <th>Date de fin</th>
            <th>Visible lors de la connexion</th>
            <th>Visible dans l'application</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/scrolling-messages/list.js')
@endsection
