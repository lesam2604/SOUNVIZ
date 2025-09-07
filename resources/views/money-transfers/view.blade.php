@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/money-transfers"><i class="fas fa-list"></i> Liste des transferts</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Expéditeur</th>
            <td id="sender"></td>
          </tr>
          <tr>
            <th>Code Expéditeur</th>
            <td id="senderCode"></td>
          </tr>
          <tr>
            <th>Récepteur</th>
            <td id="recipient"></td>
          </tr>
          <tr>
            <th>Code Récepteur</th>
            <td id="recipientCode"></td>
          </tr>
          <tr>
            <th>Montant</th>
            <td id="amount"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/money-transfers/view.js')
@endsection
