@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/scrolling-messages"><i class="fas fa-list"></i> Liste des messages
        défilants</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Libelle</th>
            <td id="label"></td>
          </tr>
          <tr>
            <th>Contenu</th>
            <td id="content"></td>
          </tr>
          <tr>
            <th>Date de debut</th>
            <td id="from"></td>
          </tr>
          <tr>
            <th>Date de fin</th>
            <td id="to"></td>
          </tr>
          <tr>
            <th>Temps de defilement (en secondes)</th>
            <td id="time"></td>
          </tr>
          <tr>
            <th>Taille du texte</th>
            <td id="size"></td>
          </tr>
          <tr>
            <th>Couleur du texte</th>
            <td id="color"></td>
          </tr>
          <tr>
            <th>Visible lors de la connexion</th>
            <td id="show_auth"></td>
          </tr>
          <tr>
            <th>Visible dans l'application</th>
            <td id="show_app"></td>
          </tr>
          <tr>
            <th>Status</th>
            <td id="status"></td>
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
      <button type="button" class="btn btn-lg btn-outline-success" id="enable">
        <i class="fas me-2 fa-check"></i> Activer</button>
      <button type="button" class="btn btn-lg btn-outline-danger" id="disable">
        <i class="fas me-2 fa-ban"></i> Désactiver</button>
      <button type="button" class="btn btn-lg btn-danger" id="delete"><i class="fas me-2 fa-trash"></i>
        Supprimer</button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/scrolling-messages/view.js')
@endsection
