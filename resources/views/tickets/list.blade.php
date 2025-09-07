@extends('layouts.app')

@section('pageTitle', "Assistances services")

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Assistances services</h4>
      <select class="form-select w-auto mx-2" id="status">
        <option value="">Tout</option>
        <option value="responded">Répondu</option>
        <option value="not-responded">Non répondu</option>
      </select>
      <a class="btn btn-outline-primary ms-auto" href="/tickets/create">
        <i class="fas fa-plus"></i> Demander une nouvelle assistance
      </a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Problème</th>
            <th>Réponse</th>
            <th>Partenaire</th>
            <th>Code partenaire</th>
            <th>Créé le</th>
            <th>Répondu le</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $objStatus }}" id="objStatus">
@endsection

@section('pageJs')
  @vite('resources/js/tickets/list.js')
@endsection
