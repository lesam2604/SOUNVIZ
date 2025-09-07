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
  <!-- ====== Carte MON SOLDE ====== -->
<div class="row mb-3">
  <div class="col-12 col-lg-4 col-md-6">
    <div class="card">
      <div class="card-body px-4 py-4">
        <div class="row">
          <div class="col-3 d-flex justify-content-start">
            <div class="stats-icon green mb-2">
              <i class="fas fa-wallet"></i>
            </div>
          </div>
          <div class="col-9">
            <h6 class="text-muted font-semibold">Mon solde</h6>
            <h6 class="font-extrabold mb-0">
              <span id="myBalanceAmount">0 FCFA</span>
    
            </h6>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- ====== /Carte MON SOLDE ====== -->


  <section class="row">
    <div class="col-12">
      <ul class="nav nav-pills" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link active" id="tabOperations" data-bs-toggle="tab" href="#operations" role="tab"
            aria-controls="operations" aria-selected="true">Gestion de opérations</a>
        </li>
        <li class="nav-pills" role="presentation">
          <a class="nav-link" id="tabInventory" data-bs-toggle="tab" href="#inventory" role="tab"
            aria-controls="inventory" aria-selected="false">Gestion de stock</a>
        </li>
        <li class="nav-pills" role="presentation">
          <a class="nav-link" id="tabStaff" data-bs-toggle="tab" href="#staff" role="tab" aria-controls="staff"
            aria-selected="false">Gestion du personnel</a>
        </li>
      </ul>

      <div class="tab-content mt-4">
        <div class="tab-pane fade show active" id="operations" role="tabpanel" aria-labelledby="tabOperations">
          <div class="row mb-3" id="newOperationBlock">
            <div class="col-12 col-lg-6">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <select id="newOpType" class="form-select me-2" style="min-width:280px"></select>
                    <a id="newOpBtn" class="btn btn-primary disabled" href="#"><i class="fas fa-plus"></i> Nouvelle opération</a>
                  </div>
                  <div class="form-text">Choisissez un type d’opération puis cliquez sur “Nouvelle opération”.</div>
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
                        <i class="fas fa-paper-plane"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Transferts</h6>
                      <h6 class="font-extrabold mb-0" id="money_transfers">0</h6>
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
                        <i class="fas fa-file-invoice"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Factures</h6>
                      <h6 class="font-extrabold mb-0">Créer et gérer</h6>
                      <div class="d-flex gap-2 justify-content-end">
                        <a href="/invoices" class="btn btn-sm btn-outline-primary" title="Liste des factures"><i class="fas fa-list"></i></a>
                        <a href="/invoices/create" class="btn btn-sm btn-primary" title="Nouvelle facture"><i class="fas fa-plus"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="tabInventory">
          <div class="row">
            <h5>Stock</h5>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon blue mb-2">
                        <i class="fas fa-list"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Categories</h6>
                      <h6 class="font-extrabold mb-0" id="inv_categories">0</h6>
                      <div class="text-end">
                        <a href="/inv-categories" title="Accéder a la liste"
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
                        <i class="fas fa-box"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Produits</h6>
                      <h6 class="font-extrabold mb-0" id="inv_products">0</h6>
                      <div class="text-end">
                        <a href="/inv-products" title="Accéder a la liste"
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
                        <i class="fas fa-truck-loading"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Approvisionnements</h6>
                      <h6 class="font-extrabold mb-0" id="inv_supplies">0</h6>
                      <div class="text-end">
                        <a href="/inv-supplies" title="Accéder a la liste"
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
                        <i class="fas fa-shopping-cart"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Commandes</h6>
                      <h6 class="font-extrabold mb-0" id="inv_orders">0</h6>
                      <div class="text-end">
                        <a href="/inv-orders" title="Accéder a la liste"
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
                        <i class="fas fa-truck"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Livraisons</h6>
                      <h6 class="font-extrabold mb-0" id="inv_deliveries">0</h6>
                      <div class="text-end">
                        <a href="/inv-deliveries" title="Accéder a la liste"
                          class="card-link btn btn-sm btn-outline-primary"><i class="fas fa-list"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="staff" role="tabpanel" aria-labelledby="tabStaff">
          <div class="row">
            <h5>Partenaires</h5>
            <div class="col-12 col-lg-4 col-md-6">
              <div class="card">
                <div class="card-body px-4 py-4">
                  <div class="row">
                    <div class="col-3 d-flex justify-content-start">
                      <div class="stats-icon blue mb-2">
                        <i class="fas fa-clock"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Partenaires en attente</h6>
                      <h6 class="font-extrabold mb-0" id="partner_pending">0</h6>
                      <div class="text-end">
                        <a href="/partners/pending" title="Accéder a la liste"
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
                        <i class="fas fa-toggle-on"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Partenaires actifs</h6>
                      <h6 class="font-extrabold mb-0" id="partner_enabled">0</h6>
                      <div class="text-end">
                        <a href="/partners/enabled" title="Accéder a la liste"
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
                        <i class="fas fa-ban"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Partenaires désactivés</h6>
                      <h6 class="font-extrabold mb-0" id="partner_disabled">0</h6>
                      <div class="text-end">
                        <a href="/partners/disabled" title="Accéder a la liste"
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
                        <i class="fas fa-thumbs-down"></i>
                      </div>
                    </div>
                    <div class="col-9">
                      <h6 class="text-muted font-semibold">Partenaires rejetés</h6>
                      <h6 class="font-extrabold mb-0" id="partner_rejected">0</h6>
                      <div class="text-end">
                        <a href="/partners/rejected" title="Accéder a la liste"
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
              <h4>Partenaires récents</h4>
            </div>
            <div class="card-content pb-4" id="recentPartners"></div>
          </div>
        </div>
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
  @vite('resources/js/dashboards/collab.js')
@endsection
