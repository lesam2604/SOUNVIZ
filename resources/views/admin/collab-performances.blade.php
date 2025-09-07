@extends('layouts.app')

@section('pageContent')
<div class="card">
  <div class="card-header d-flex align-items-center">
    <h4 class="mb-0">Performances des collaborateurs</h4>
    <a class="btn btn-outline-primary ms-auto" href="/dashboard"><i class="fas fa-home"></i> Dashboard</a>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-12 col-lg-3">
        <label for="from_date" class="form-label">Du</label>
        <input type="date" id="from_date" class="form-control">
      </div>
      <div class="col-12 col-lg-3">
        <label for="to_date" class="form-label">Au</label>
        <input type="date" id="to_date" class="form-control">
      </div>
      <div class="col-12 col-lg-3 d-flex align-items-end">
        <button id="btnFilter" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered" id="tbl">
        <thead>
          <tr>
            <th>Collaborateur</th>
            <th>Email</th>
            <th>Opérations validées</th>
            <th>Montant total (FCFA)</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <hr>
    <h5>Détails par type d'opération</h5>
    <div class="table-responsive">
      <table class="table table-bordered" id="tblDetail">
        <thead>
          <tr>
            <th>Collaborateur</th>
            <th>Type d'opération</th>
            <th>Opérations</th>
            <th>Montant (FCFA)</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <th colspan="2" class="text-end">TOTAL GÉNÉRAL</th>
            <th id="detailTotalCount">0</th>
            <th id="detailTotalAmount">0</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection

@section('pageJs')
  @vite('resources/js/admin/collab-performances.js')
@endsection
