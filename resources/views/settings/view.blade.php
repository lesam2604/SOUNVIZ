@extends('layouts.app')

@section('pageTitle', 'Paramètres')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="page-title">
    <div class="row">
      <div class="col-12 col-md-8 order-md-1 order-last">
        <h2>Paramètres</h2>
      </div>
    </div>
  </div>

  <ul class="nav nav-pills" id="myTab" role="tablist">
    <li class="nav-pills" role="presentation">
      <a class="nav-link" id="tabOthers" data-bs-toggle="tab" href="#paneOthers" role="tab" aria-controls="paneOthers"
        aria-selected="false">Autres paramètres</a>
    </li>
  </ul>

  <div class="tab-content mt-4" id="tabPanes">
    <div class="tab-pane fade" id="paneOthers" role="tabpanel" aria-labelledby="tabOthers">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h3 class="mb-0">Message du tableau de bord</h3>
        </div>

        <div class="card-body">
          <form>
            <textarea class="form-control" id="dashboardMessage" cols="30" rows="10"></textarea>
          </form>

          <div class="text-center mt-3">
            <button type="button" class="btn btn-primary btn-lg" id="updateDashboardMessage">
              <i class="fas fa-save"></i> Sauvegarder
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/settings/view.js')
@endsection
