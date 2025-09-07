@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/withdrawals"><i class="fas fa-list"></i> Liste des retraits</a>
    </div>
    <div class="card-body" style="overflow-x: auto;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Code</th>
            <td id="code"></td>
          </tr>
          <tr>
            <th>Montant</th>
            <td id="amount"></td>
          </tr>
          <tr>
            <th>Effectuée le</th>
            <td id="createdAt"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite('resources/js/withdrawals/view.js')
@endsection
