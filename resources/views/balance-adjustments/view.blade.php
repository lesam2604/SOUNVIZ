@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/balance-adjustments">
        <i class="fas fa-list"></i> Liste des ajustements
      </a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Partenaire</th>
            <td id="partner"></td>
          </tr>
          <tr>
            <th>Code Partenaire</th>
            <td id="codePartner"></td>
          </tr>
          <tr>
            <th>Ancien solde</th>
            <td id="old_balance"></td>
          </tr>
          <tr>
            <th>Nouveau solde</th>
            <td id="balance"></td>
          </tr>
          <tr>
            <th>Motif de l'ajustement</th>
            <td id="reason"></td>
          </tr>
          <tr>
            <th>Effectue le</th>
            <td id="createdAt"></td>
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

    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/balance-adjustments/view.js')
@endsection
