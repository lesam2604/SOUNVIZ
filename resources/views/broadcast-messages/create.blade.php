@extends('layouts.app')

@section('cssPlugins')
  <link rel="stylesheet" href="/assets/extensions/quill/quill.snow.css">
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/broadcast-messages"><i class="fas fa-list"></i> Liste des messages
        de diffusion</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="label" class="form-label">Libellé</label>
          <input type="text" class="form-control" id="label" placeholder="Libellé du message" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="group" class="form-label">Groupe</label>
          <select id="group" class="form-select" required>
            <option value="all">Tous les utilisateurs</option>
            <option value="collab">Collaborateurs uniquement</option>
            <option value="partner">Partenaires uniquement</option>
          </select>
        </div>

        <div class="col-12 mb-3">
          <label for="content" class="form-label">Contenu</label>
          <div>
            <div id="content"></div>
          </div>
          <div class="invalid-feedback"></div>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-broadcast-tower"></i> Envoyer</button>
        </div>
      </form>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('jsPlugins')
  <script src="/assets/extensions/quill/quill.min.js"></script>
@endsection

@section('pageJs')
  @vite('resources/js/broadcast-messages/create.js')
@endsection
