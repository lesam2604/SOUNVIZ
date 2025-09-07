@extends('layouts.app')

@section('pageTitle', 'Commissions de la plateforme')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Commissions de la plateforme: Total <span class="text-danger" id="total">0</span> FCFA</h4>
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
            <th>#</th>
            <th>Code</th>
            <th>Opération</th>
            <th>Commission</th>
            <th>Date</th>
            <th>Partenaire</th>
            <th>Code partenaire</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/commissions/list-platform.js')
@endsection
