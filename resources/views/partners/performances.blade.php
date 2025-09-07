@extends('layouts.app')

@section('pageTitle', 'Performances')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Performances</h4>
      <span id="spinner" class="spinner-border text-primary ms-2" role="status" style="display: none;"></span>
    </div>
    <div class="card-body">
      <form id="formFilter" class="border-bottom border-top pt-3 mb-3">
        <div class="row">
          <div class="mb-3 col-lg-6 d-flex align-items-center">
            <label for="partnerId" class="form-label me-2 mb-0">Partenaire:</label>
            <select class="form-select" id="partnerId"></select>
          </div>
          <div class="mb-3 col-lg-3 d-flex align-items-center">
            <label for="fromDate" class="form-label me-2 mb-0">De:</label>
            <input type="date" class="form-control" id="fromDate">
          </div>
          <div class="mb-3 col-lg-3 d-flex align-items-center">
            <label for="toDate" class="form-label me-2 mb-0">A:</label>
            <input type="date" class="form-control" id="toDate">
          </div>
        </div>
      </form>
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th></th>
            <th>Nombre</th>
            <th>Montant</th>
            <th>Frais</th>
            <th>Commissions</th>
            <th>Commissions Plateforme</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/partners/performances.js')
@endsection
