@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/tickets">
        <i class="fas fa-list"></i> Liste des assistances services
      </a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Problème</th>
            <td id="issue"></td>
          </tr>
          <tr>
            <th>Réponse</th>
            <td id="response"></td>
          </tr>
          <tr>
            <th>Répondue par</th>
            <td id="responder"></td>
          </tr>
          <tr>
            <th>Répondue le</th>
            <td id="respondedAt"></td>
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
        data-permission="edit ticket">Éditer</button>
      <button type="button" class="btn btn-lg btn-primary" id="respond"
        data-permission="respond ticket">Répondre</button>
      <button type="button" class="btn btn-lg btn-danger" id="delete"
        data-permission="delete ticket">Supprimer</button>
    </div>
  </div>

  <div class="modal" id="modalRespond" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalRespond" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRespondLabel">Réponse</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body row">
          <form id="respond-form" class="row">
            <div class="col-12 mb-3">
              <label for="ourResponse" class="form-label">Notre réponse</label>
              <textarea id="ourResponse" rows="15" class="form-control" maxlength="5000"
                placeholder="Entrez votre réponse détaillée ici..."></textarea>
              <div class="invalid-feedback"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button id="submitResponse" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/tickets/view.js')
@endsection
