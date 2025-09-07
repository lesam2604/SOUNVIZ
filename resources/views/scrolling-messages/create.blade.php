@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/scrolling-messages"><i class="fas fa-list"></i> Liste des messages
        défilants</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="label" class="form-label">Libelle</label>
          <input type="text" class="form-control" id="label" placeholder="Libelle du message" required
            maxlength="191">
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="content" class="form-label">Contenu</label>
          <textarea id="content" class="form-control" required maxlength="1000" placeholder="Contenu"></textarea>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="from" class="form-label">Date de debut</label>
          <input type="date" class="form-control" id="from" placeholder="Date de debut" required>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="to" class="form-label">Date de fin</label>
          <input type="date" class="form-control" id="to" placeholder="Date de fin" required>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="time" class="form-label">Temps de defilement (en secondes)</label>
          <input type="text" class="form-control" id="time" placeholder="Temps de defilement du message" required>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="size" class="form-label">Taille de texte</label>
          <select id="size" class="form-select" required>
            <option value="small">Petit</option>
            <option value="medium">Moyen</option>
            <option value="large">Grand</option>
          </select>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="color" class="form-label">Couleur du texte</label>
          <select id="color" class="form-select" required>
            <option value="black">Noir</option>
            <option value="blue">Blue</option>
            <option value="red">Rouge</option>
            <option value="yellow">Jaune</option>
            <option value="green">Vert</option>
          </select>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="show_auth" class="form-label">Visible lors de la connexion</label>
          <select id="show_auth" class="form-select">
            <option value="0">Non</option>
            <option value="1">Oui</option>
          </select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
          <label for="show_app" class="form-label">Visible dans l'application</label>
          <select id="show_app" class="form-select">
            <option value="0">Non</option>
            <option value="1">Oui</option>
          </select>
          <div class="invalid-feedback"></div>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/scrolling-messages/create.js')
@endsection
