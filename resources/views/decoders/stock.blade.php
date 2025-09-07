@extends('layouts.app')

@section('pageTitle', 'Stock de décodeurs partenaires')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title">
        <span data-role="reviewer">Stock de décodeurs partenaires</span>
        <span data-role="partner-master">Mon stock de décodeurs</span>
      </h4>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-lg-4 col-md-6">
          <div class="card cursor-pointer" data-status="">
            <div class="card-body px-4 py-4">
              <div class="row">
                <div class="col-3 d-flex justify-content-start">
                  <div class="stats-icon blue mb-2">
                    <i class="fas fa-tv"></i>
                  </div>
                </div>
                <div class="col-9">
                  <h6 class="text-muted font-semibold">Décodeurs achetés</h6>
                  <h6 class="font-extrabold mb-0" id="allDecoders">0</h6>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4 col-md-6">
          <div class="card cursor-pointer" data-status="activated">
            <div class="card-body px-4 py-4">
              <div class="row">
                <div class="col-3 d-flex justify-content-start">
                  <div class="stats-icon green mb-2">
                    <i class="fas fa-tv"></i>
                  </div>
                </div>
                <div class="col-9">
                  <h6 class="text-muted font-semibold">Décodeurs activés</h6>
                  <h6 class="font-extrabold mb-0" id="activatedDecoders">0</h6>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4 col-md-6">
          <div class="card cursor-pointer" data-status="not_activated">
            <div class="card-body px-4 py-4">
              <div class="row">
                <div class="col-3 d-flex justify-content-start">
                  <div class="stats-icon red mb-2">
                    <i class="fas fa-tv"></i>
                  </div>
                </div>
                <div class="col-9">
                  <h6 class="text-muted font-semibold">Décodeurs non activés</h6>
                  <h6 class="font-extrabold mb-0" id="notActivatedDecoders">0</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <form id="formFilter" class="border-bottom border-top pt-3 mb-3">
        <div class="row">
          <div class="mb-3 col-lg-6 d-flex align-items-center">
            <label for="companyId" class="form-label me-2 mb-0">Établissement:</label>
            <select class="form-select" id="companyId"></select>
          </div>
          <div class="mb-3 col-lg-3 d-flex align-items-center">
            <label for="status" class="form-label me-2 mb-0">Statut</label>
            <select class="form-select" id="status">
              <option value="">Tout</option>
              <option value="activated">Activé</option>
              <option value="not_activated">Non activé</option>
            </select>
          </div>
        </div>
      </form>
      <table class="table" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Numéro</th>
            <th>Commande</th>
            <th>Établissement</th>
            <th>Partenaire</th>
            <th>Activé</th>
            <th>Opération</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" id="paramStatus" value="{{ $status }}">
@endsection

@section('pageJs')
  @vite('resources/js/decoders/stock.js')
@endsection
