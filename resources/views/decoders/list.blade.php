@extends('layouts.app')

@section('pageTitle', 'Décodeurs reconnus')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title">Décodeurs reconnus</h4>
      <a class="btn btn-outline-primary ms-auto" href="/decoders/create">
        <i class="fas fa-plus"></i> Ajouter de nouveaux décodeurs
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
            <th>Numéro</th>
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
          <h5 class="modal-title">Suppression de décodeurs</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 col-lg-6 mb-3">
              <label for="decoderNumberFrom" class="form-label">Numéro de début</label>
              <input type="text" class="form-control" id="decoderNumberFrom" placeholder="Numéro de début" required
                minlength="14" maxlength="14">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 col-lg-6 mb-3">
              <label for="decoderNumberTo" class="form-label">Numéro de fin</label>
              <input type="text" class="form-control" id="decoderNumberTo" placeholder="Numéro de fin" required
                minlength="14" maxlength="14">
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
  @vite('resources/js/decoders/list.js')
@endsection
