@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        <div class="col-12 col-lg-6 mb-3">
          <label for="partnerId" class="form-label">Partenaire</label>
          <select id="partnerId" class="form-select"></select>
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="ubaType" class="form-label">Catégorie</label>
          <select id="ubaType" class="form-select"></select>
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="fromDate" class="form-label">Date de debut</label>
          <input type="date" class="form-control" id="fromDate" placeholder="Date de debut">
        </div>
        <div class="col-12 col-lg-6 mb-3">
          <label for="toDate" class="form-label">Date de fin</label>
          <input type="date" class="form-control" id="toDate" placeholder="Date de fin">
        </div>

        <div class="text-center my-3">
          <button type="submit" class="btn btn-primary"><i class="fas me-2 fa-file-pdf"></i> Exportation PDF</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/operations/card-activation-export.js')
@endsection
