@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="" id="linkList"></a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <div class="alert alert-danger" role="alert" id="alertMessage" style="display: none;"></div>

      <table class="table table-bordered" id="tableOperation">
        <tbody>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3" id="blockCommissions">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Commissions</h4>
    </div>
    <div class="card-body">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Montant de l'opÃ©ration</th>
            <td id="amount" class="fw-bold"></td>
          </tr>
          <tr>
            <th>Frais de course</th>
            <td id="fee" class="fw-bold"></td>
          </tr>
          <tr>
            <th>Montant total</th>
            <td id="totalAmount" class="fw-bold"></td>
          </tr>
          <tr>
            <th>Commission du partenaire</th>
            <td id="commission" class="fw-bold"></td>
          </tr>
          <tr>
            <th>Commission de la plateforme</th>
            <td id="commissionPlatform" class="fw-bold"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Partenaire</h4>
    </div>
    <div class="card-body">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="codePartner"></td>
          </tr>
          <tr>
            <th>PrÃ©nom</th>
            <td id="firstName"></td>
          </tr>
          <tr>
            <th>Nom</th>
            <td id="lastName"></td>
          </tr>
          <tr>
            <th>Ã‰tablissement</th>
            <td id="companyName"></td>
          </tr>
          <tr>
            <th>NumÃ©ro IFU</th>
            <td id="tin"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Statut</h4>
    </div>
    <div class="card-body">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Statut de l'opÃ©ration</th>
            <td id="status"></td>
          </tr>
          <tr>
            <th>Revue par</th>
            <td id="reviewer"></td>
          </tr>
          <tr>
            <th>Revue le</th>
            <td id="reviewedAt"></td>
          </tr>
          <tr>
            <th>Feedback</th>
            <td id="feedback"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Actions</h4>
    </div>
    <div class="card-body">
      <button type="button" class="btn btn-lg btn-outline-primary" id="edit" data-permission="edit operation">
        <i class="fas me-2 fa-pen"></i> Éditer
      </button>
      <button type="button" class="btn btn-lg btn-outline-success" id="approve" data-permission="review operation">
        <i class="fas me-2 fa-thumbs-up"></i> Approuver
      </button>
      <button type="button" class="btn btn-lg btn-outline-info" id="approveWithoutCommission" data-permission="review operation">
        <i class="fas me-2 fa-thumbs-up"></i> Approuver sans les commissions
      </button>
      <button type="button" class="btn btn-lg btn-outline-warning" id="createInvoice">
        <i class="fas me-2 fa-file-invoice"></i> Créer une facture
      </button>
      <button type="button" class="btn btn-lg btn-outline-danger" id="reject" data-permission="review operation">
        <i class="fas me-2 fa-thumbs-down"></i> Rejeter
      </button>    </div>
  </div>

  <input type="hidden" value="{{ $opTypeCode }}" id="opTypeCode">
  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/operations/view.js')
@endsection

