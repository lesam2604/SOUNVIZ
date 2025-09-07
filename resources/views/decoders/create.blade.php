@extends('layouts.app')

@section('cssPlugins')
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0" id="title"></h4>
      <a class="btn btn-outline-primary ms-auto" href="/decoders"><i class="fas fa-list"></i> Liste des décodeurs
        reconnus</a>
    </div>
    <div class="card-body">
      <form id="form" class="row" novalidate>
        @include('partials.decoder-adding-types')

        <div class="text-center">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
      </form>
    </div>
  </div>

  <input type="hidden" value="{{ $objectId ?? '' }}" id="objectId">
@endsection

@section('pageJs')
  @vite(['resources/js/partials/decoder-adding-types.js', 'resources/js/decoders/create.js'])
@endsection
