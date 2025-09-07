@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/extra-clients"><i class="fas fa-list"></i>
        Liste des clients extra</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Nom d’établissement</th>
            <td id="companyName"></td>
          </tr>
          <tr>
            <th>Numéro IFU</th>
            <td id="tin"></td>
          </tr>
          <tr>
            <th>Numéro de téléphone</th>
            <td id="phoneNumber"></td>
          </tr>
          <tr>
            <th>Prénom</th>
            <td id="firstName"></td>
          </tr>
          <tr>
            <th>Nom</th>
            <td id="lastName"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Actions</h4>
    </div>
    <div class="card-body">
      <button type="button" class="btn btn-lg btn-outline-primary" id="edit"><i class="fas me-2 fa-pen"></i>
        Éditer</button>
      <button type="button" class="btn btn-lg btn-danger" id="delete"><i class="fas me-2 fa-trash"></i>
        Supprimer</button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/extra-clients/view.js')
@endsection
