@extends('layouts.app')

@section('cssPlugins')
  <link rel="stylesheet" href="/assets/extensions/quill/quill.snow.css">
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/broadcast-messages"><i class="fas fa-list"></i> Liste des messages
        de diffusion</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Libellé</th>
            <td id="label"></td>
          </tr>
          <tr>
            <th>Groupe</th>
            <td id="group"></td>
          </tr>
          <tr>
            <th>Contenu</th>
            <td id="content"></td>
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
      <button type="button" class="btn btn-lg btn-outline-primary" id="edit"
        data-permission="edit broadcast_message"><i class="fas me-2 fa-pen"></i> Éditer</button>
      <button type="button" class="btn btn-lg btn-danger" id="delete" data-permission="delete broadcast_message"><i
          class="fas me-2 fa-trash"></i> Supprimer</button>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/broadcast-messages/view.js')
@endsection
