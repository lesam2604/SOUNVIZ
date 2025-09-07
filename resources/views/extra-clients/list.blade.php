@extends('layouts.app')

@section('pageTitle', 'Clients Extra')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <h4 class="mb-0">Clients Extra</h4>
      </div>
      <a class="btn btn-outline-primary" href="/extra-clients/create" data-permission="add extra-client">
        <i class="fas fa-plus"></i> Ajouter un nouveau client extra
      </a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Établissement</th>
            <th>IFU</th>
            <th>Téléphone</th>
            <th>Prénom</th>
            <th>Nom</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/extra-clients/list.js')
@endsection
