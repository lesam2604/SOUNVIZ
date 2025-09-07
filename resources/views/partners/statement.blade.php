@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <div class="dropdown ms-auto">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
          aria-expanded="false"><i class="fas fa-file-export me-1"></i> Exporter</button>
        <ul class="dropdown-menu">
          <li data-permission="export-excel statement">
            <a class="dropdown-item" href="#" id="exportExcel"><i class="fas me-2 fa-file-excel"></i> Excel</a>
          </li>
          <li data-permission="export-pdf statement">
            <a class="dropdown-item" href="#" id="exportPdf"><i class="fas me-2 fa-file-pdf"></i> PDF</a>
          </li>
        </ul>
      </div>
    </div>
    <div class="card-body">
      <form id="formFilter" class="border-bottom pt-3 mb-3">
        <div class="row">
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
            <th>Date</th>
            <th>Partenaire</th>
            <th>Opération</th>
            <th>Montant</th>
            <th>Solde</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/partners/statement-list.js')
@endsection
