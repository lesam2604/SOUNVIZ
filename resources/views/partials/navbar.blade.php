<nav class="navbar navbar-expand navbar-light navbar-top">
  <div class="container-fluid">
    <a href="#" class="burger-btn d-block">
      <i class="bi bi-justify fs-3"></i>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-lg-0">
        <li class="nav-item me-1" data-permission="view balance_adjustment">
          <a class="nav-link active text-gray-600" href="/balance-adjustments" title="Ajustement de solde">
            <i class='fas fa-balance-scale fs-4 mt-2'></i>
          </a>
        </li>
        <li class="nav-item me-1" data-permission="view scrolling_message">
          <a class="nav-link active text-gray-600" href="/scrolling-messages" title="Message défilants">
            <i class='fas fa-scroll fs-4 mt-2'></i>
          </a>
        </li>
        <li class="nav-item me-1 position-relative">
          <a class="nav-link active text-gray-600" href="/tickets/not-responded" title="Assistances services">
            <i class='fas fa-info-circle fs-4 mt-2'></i>
            <span id="badgeTickets" class="position-absolute badge rounded-pill bg-danger"
              style="top: 5px; left: 11px; display: none;"></span>
          </a>
        </li>
        <li class="nav-item me-1 position-relative">
          <a class="nav-link active text-gray-600" href="/broadcast-messages" title="Messages de diffusion">
            <i class='fas fa-broadcast-tower fs-4 mt-2'></i>
            <span id="badgeBroadcastMessages" class="position-absolute badge rounded-pill bg-danger"
              style="top: 5px; left: 11px; display: none;"></span>
          </a>
        </li>
        <li class="nav-item me-3 position-relative">
          <a class="nav-link active text-gray-600" href="/notifications" title="Notifications" data-bs-display="static"
            id="notification-bell">
            <i class='fas fa-bell fs-4 mt-2'></i>
            <span id="badgeNotifications" class="position-absolute badge rounded-pill bg-danger"
              style="top: 5px; left: 11px; display: none;"></span>
          </a>
        </li>
      </ul>
      <div class="dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="user-menu d-flex">
            <div class="user-name text-end me-3">
              <h6 class="mb-0 text-gray-600 user-full-name"></h6>
              <p class="mb-0 text-sm text-gray-600 user-type"></p>
            </div>
            <div class="user-img d-flex align-items-center">
              <div class="avatar avatar-md">
                <img src="" alt="user-picture" class="user-picture">
              </div>
            </div>
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="min-width: 11rem;">
          <li>
            <h6 class="dropdown-header">Hello, <span class="user-first-name"></span>!</h6>
          </li>
          {{-- <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-person me-2"></i> Mon Profile</a></li> --}}
          <li data-permission="set setting"><a class="dropdown-item" href="/settings"><i
                class="icon-mid bi bi-gear me-2"></i>
              Paramètres</a></li>
          <li><a class="dropdown-item" href="#" id="changePassword">
              <i class="icon-mid bi bi-shield-lock me-2"></i> Changer votre mot de passe</a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="#" id="logout">
              <i class="icon-mid bi bi-box-arrow-left me-2"></i> Déconnecter</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
