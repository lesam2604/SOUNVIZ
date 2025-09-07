@extends('layouts.app')

@section('pageTitle', 'Cartes reconnues')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title">Cartes reconnues</h4>
      <a class="btn btn-outline-primary ms-auto" href="/cards/create">
        <i class="fas fa-plus"></i> Ajouter de nouvelles cartes
      </a>
      <button class="btn btn-danger ms-2" id="showModalDeleteRange">
        <i class="fas fa-trash"></i> Suppression multiple
      </button>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Id</th>
            <th>Catégorie</th>
            <th>Vendu</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <div class="modal" tabindex="-1" id="modalDeleteRange">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Suppression de cartes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 col-lg-6 mb-3">
              <label for="cardIdFrom" class="form-label">Id de début</label>
              <input type="text" class="form-control" id="cardIdFrom" placeholder="Id de début" required minlength="10"
                maxlength="10">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 col-lg-6 mb-3">
              <label for="cardIdTo" class="form-label">Id de fin</label>
              <input type="text" class="form-control" id="cardIdTo" placeholder="Id de fin" required minlength="10"
                maxlength="10">
              <div class="invalid-feedback"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="deleteRange">
            <i class="fas fa-trash"></i> Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/cards/list.js')
@endsection
