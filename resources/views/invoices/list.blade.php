@extends('layouts.app')

@section('pageContent')
<div class="card">
  <div class="card-header d-flex align-items-center">
    <h4 class="mb-0">Factures</h4>
    <a class="btn btn-primary ms-auto" href="/invoices/create"><i class="fas fa-plus"></i> Nouvelle facture</a>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-12 col-lg-3">
        <label for="status" class="form-label">Statut</label>
        <select id="status" class="form-select">
          <option value="">Tous</option>
          <option value="unpaid">Impayées</option>
          <option value="paid">Payées</option>
        </select>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered" id="tbl">
        <thead>
          <tr>
            <th>Code</th>
            <th>Type d'opération</th>
            <th>Client</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('pageJs')
  @vite('resources/js/invoices/list.js')
@endsection

