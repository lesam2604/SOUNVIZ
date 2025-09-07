@extends('layouts.app')

@section('pageTitle', 'Activités')

@section('cssPlugins')

@endsection

@section('pageCss')
  <style>
    .section-50 {
      padding: 50px 0;
    }

    .m-b-50 {
      margin-bottom: 50px;
    }

    .dark-link {
      color: #333;
    }

    .heading-line {
      position: relative;
      padding-bottom: 5px;
    }

    .heading-line:after {
      content: "";
      height: 4px;
      width: 75px;
      background-color: #29B6F6;
      position: absolute;
      bottom: 0;
      left: 0;
    }

    .notification-ui_dd-content {
      margin-bottom: 30px;
    }

    .notification-list {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-pack: justify;
      -ms-flex-pack: justify;
      justify-content: space-between;
      padding: 20px;
      margin-bottom: 7px;
      background: #fff;
      -webkit-box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
    }

    .notification-list--unread {
      border-left: 5px solid #29B6F6;
    }

    .notification-list .notification-list_content .notification-list_img i,
    .notification-list--unread .notification-list_detail .subject {
      color: #29B6F6;
    }

    .notification-list .notification-list_content {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
    }

    .notification-list .notification-list_content .notification-list_img i {
      font-size: 48px;
      margin-right: 20px;
    }

    .notification-list .notification-list_content .notification-list_detail p {
      margin-bottom: 5px;
      line-height: 1.2;
    }
  </style>
@endsection

@section('pageContent')
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <h4 class="mb-0">Activités</h4>
    </div>
    <div class="card-body">
      <div class="notification-ui_dd-content" id="notifications">
      </div>

      <div class="text-center">
        <a href="#!" class="dark-link" id="loadMore">Charger plus</a>
      </div>
    </div>
  </div>
@endsection

@section('pageJs')
  @vite('resources/js/histories/list.js')
@endsection
