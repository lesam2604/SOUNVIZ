@extends('layouts.auth')

@section('pageTitle', 'Email verification')

@section('pageContent')
  <div class="text-center m-auto">
    <?php
    $text = $status == 'success' ? 'text-success' : 'text-danger';
    $alert = $status == 'success' ? 'alert-success' : 'alert-danger';
    $mdi = $status == 'success' ? 'mdi-check' : 'mdi-alert-outline';
    ?>

    <i class="mdi mdi-security {{ $text }}" style="font-size: 50px;"></i>
    <h4 class="text-dark-50 text-center mt-2 fw-bold">Email verification {{ $status }}</h4>

    @foreach ($messages as $message)
      <div class="alert {{ $alert }} fade show" role="alert">
        <i class="mdi {{ $mdi }}"></i>
        {{ $message }}
      </div>
    @endforeach
  </div>
@endsection

@section('pageAfterContent')
  <div class="row mt-3">
    <div class="col-12 text-center">
      <p class="text-muted">Back to <a href="/login" class="text-muted ms-1"><b>Log In</b></a></p>
    </div>
  </div>
@endsection
