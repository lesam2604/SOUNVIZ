@extends('layouts.app')

@section('pageTitle', 'Categories de produits')

@section('cssPlugins')

@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Categories de produits</h4>
      <a class="btn btn-outline-primary ms-auto" href="/inv-categories/create"><i class="fas fa-plus"></i> Ajouter une
        nouvelle catégorie de produits</a>
    </div>
    <div class="card-body">
      <table class="table table-bordered" id="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Nom</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/inv-categories/list.js')
@endsection
