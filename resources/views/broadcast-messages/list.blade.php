@extends('layouts.app')

@section('pageTitle', 'Messages de diffusion')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Messages de diffusion</h4>
      <a class="btn btn-outline-primary ms-auto" href="/broadcast-messages/create" data-permission="add broadcast_message">
        <i class="fas fa-plus"></i> Ajouter un nouveau message de diffusion</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Libellé</th>
            <th>Groupe</th>
            <th>Contenu</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/broadcast-messages/list.js')
@endsection
