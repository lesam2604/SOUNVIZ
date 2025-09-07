@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <select class="form-select w-auto mx-2" id="status">
        <option value="">Tout</option>
        <option value="pending">En attente</option>
        <option value="approved">Validées</option>
        <option value="rejected">Rejetées</option>
      </select>

      <div class="dropdown ms-auto">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
          aria-expanded="false"><i class="fas fa-file-export me-1"></i> Exporter</button>
        <ul class="dropdown-menu">
          <li data-permission="export-excel operation">
            <a class="dropdown-item" href="#" id="exportExcel"><i class="fas me-2 fa-file-excel"></i> Excel</a>
          </li>
          <li data-permission="export-pdf operation">
            <a class="dropdown-item" href="#" id="exportPdf"><i class="fas me-2 fa-file-pdf"></i> PDF</a>
          </li>
        </ul>
      </div>
      <a class="btn btn-outline-primary ms-2" href="" id="linkCreate" data-permission="add operation"></a>
    </div>
    <div class="card-body">
      <form id="formFilter" class="border-bottom border-top pt-3 mb-3">
        <div class="row">
          <div class="mb-3 col-lg-6 d-flex align-items-center">
            <label for="partnerId" class="form-label me-2 mb-0">Partenaire:</label>
            <select class="form-select" id="partnerId"></select>
          </div>
          <div class="mb-3 col-lg-6 d-flex align-items-center">
            <label for="cardType" class="form-label me-2 mb-0">Type de carte:</label>
            <select class="form-select" id="cardType"></select>
          </div>
          <div class="mb-3 col-lg-6 d-flex align-items-center">
            <label for="ubaType" class="form-label me-2 mb-0">Catégorie de carte:</label>
            <select class="form-select" id="ubaType"></select>
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
        <thead></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $opTypeCode }}" id="opTypeCode">
  <input type="hidden" value="{{ $opStatus }}" id="opStatus">
@endsection

@section('pageJs')
  @vite('resources/js/operations/list.js')

  
@endsection
