@extends('layouts.app')

@section('pageTitle', 'Collaborateurs')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Collaborateurs</h4>
      <select id="status" class="form-select w-auto mx-2">
        <option value="">Tout</option>
        <option value="enabled">Actifs</option>
        <option value="disabled">Inactifs</option>
      </select>
      <a class="btn btn-outline-primary ms-auto" href="/collabs/create"><i class="fas fa-plus"></i> Ajouter un nouveau
        collaborateur</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Telephone</th>
            <th>Email</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $opStatus }}" id="opStatus">
@endsection

@section('pageJs')
  @vite('resources/js/collabs/list.js')
@endsection
