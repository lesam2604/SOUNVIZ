@extends('layouts.app')

@section('pageTitle', 'Tableau de bord')

@section('pageContent')
  <div class="page-title">
    <div class="row">
      <div class="col-12 order-md-1 order-last">
        <h3>Tableau de bord</h3>
        <p class="text-subtitle text-muted" id="dashboardMessage"></p>
      </div>
    </div>
  </div>

  <section class="row">
    <div class="col-12">
      <ul class="nav nav-pills d-none" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link active" id="tabOperations" data-bs-toggle="tab" href="#operations" role="tab"
            aria-controls="operations" aria-selected="true">Gestion de opérations</a>
        </li>
      </ul>

      <div class="tab-content mt-4">
        <div class="tab-pane fade show active" id="operations" role="tabpanel" aria-labelledby="tabOperations">
          <div class="row" data-role="partner-master">
            <h5>Mon stock de cartes</h5>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon blue mb-2">
                        <i class="fas fa-credit-card"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Cartes achetées</h6>
                      <h6 class="font-extrabold mb-0" id="allCards">0</h6>
                      <div class="text-end">
                        <a href="/cards/stock" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon green mb-2">
                        <i class="fas fa-credit-card"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Cartes activées</h6>
                      <h6 class="font-extrabold mb-0" id="activatedCards">0</h6>
                      <div class="text-end">
                        <a href="/cards/stock/activated" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon red mb-2">
                        <i class="fas fa-credit-card"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Cartes non activées</h6>
                      <h6 class="font-extrabold mb-0" id="notActivatedCards">0</h6>
                      <div class="text-end">
                        <a href="/cards/stock/not_activated" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row" data-role="partner-master">
            <h5>Mon stock de décodeurs</h5>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
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
                      <div class="text-end">
                        <a href="/decoders/stock" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
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
                      <div class="text-end">
                        <a href="/decoders/stock/activated" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
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
                      <div class="text-end">
                        <a href="/decoders/stock/not_activated" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row" id="otherOps">
            <h5>Autres</h5>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon blue mb-2">
                        <i class="fas fa-money-check"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Retraits de commissions</h6>
                      <h6 class="font-extrabold mb-0" id="withdrawals">0</h6>
                      <div class="text-end">
                        <a href="/withdrawals" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon blue mb-2">
                        <i class="fas fa-angle-double-up"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Transferts effectués</h6>
                      <h6 class="font-extrabold mb-0" id="sent_money_transfers">0</h6>
                      <div class="text-end">
                        <a href="/money-transfers" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon blue mb-2">
                        <i class="fas fa-angle-double-down"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Transferts reçus</h6>
                      <h6 class="font-extrabold mb-0" id="received_money_transfers">0</h6>
                      <div class="text-end">
                        <a href="/money-transfers" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="row">
        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-header">
              <h4>Status des opérations</h4>
            </div>
            <div class="card-body">
              <div id="chartOperationStatus"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Activités récentes</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-lg" id="tableActivities">
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@section('jsPlugins')
  <script src="/assets/extensions/apexcharts/apexcharts.min.js"></script>
@endsection

@section('pageJs')
  @vite('resources/js/dashboards/partner.js')
@endsection
