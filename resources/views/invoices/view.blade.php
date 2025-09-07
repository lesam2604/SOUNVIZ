@extends('layouts.app')

@section('pageContent')
<div class="card">
  <div class="card-header d-flex align-items-center gap-2">
    <h4 class="mb-0">Facture <span id="invCode"></span></h4>
    <a class="btn btn-outline-primary ms-auto" href="/invoices"><i class="fas fa-list"></i> Liste</a>
    <button id="btnPrint" class="btn btn-outline-secondary"><i class="fas fa-print"></i> Imprimer</button>
    <a id="btnCsv" class="btn btn-outline-success" target="_blank"><i class="fas fa-file-csv"></i> CSV</a>
    <a id="btnPdf" class="btn btn-outline-danger" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
  </div>
  <div class="card-body" id="content"></div>
</div>
@endsection

@section('pageJs')
  <style>
    @media print {
      body * { visibility: hidden !important; }
      #content, #content * { visibility: visible !important; }
      #content { position: absolute; left: 0; top: 0; width: 100%; }
    }
  </style>
  @vite('resources/js/invoices/view.js')
@endsection
