<div class="col-12 col-lg-6 mb-3">
  <label for="type" class="form-label">Type d'ajout</label>
  <select id="type" class="form-select">
    <option value="one">Une carte</option>
    <option value="many">Plusieurs cartes</option>
    <option value="range">Intervalle</option>
  </select>
  <div class="invalid-feedback"></div>
</div>

<div class="col-12">
  <div class="row" id="oneBlock">
    <div class="col-12 col-lg-6 mb-3">
      <label for="cardId" class="form-label">Id</label>
      <input type="text" class="form-control" id="cardId" placeholder="Id de la carte" required minlength="10"
        maxlength="10">
      <div class="invalid-feedback"></div>
    </div>
  </div>

  <div class="row" id="manyBlock">
    <div class="col-12 col-lg-6 mb-3">
      <div class="input-group">
        <input type="text" class="form-control" id="cardIdMany" placeholder="Id de la carte" required minlength="10"
          maxlength="10">
        <button class="btn btn-outline-primary" type="button" id="addCardId"><i class="fas fa-plus"></i>
          Ajouter</button>
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 mb-3">
      <table class="table table-bordered" id="tableCardIds">
        <thead>
          <tr>
            <th>Id</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

  <div class="row" id="rangeBlock">
    <div class="col-12 col-lg-6 mb-3">
      <label for="cardIdFrom" class="form-label">Id de début</label>
      <input type="text" class="form-control" id="cardIdFrom" placeholder="Id de début" required minlength="10"
        maxlength="10">
      <div class="invalid-feedback"></div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="cardIdTo" class="form-label">Id de fin</label>
      <input type="text" class="form-control" id="cardIdTo" placeholder="Id de fin" required minlength="10"
        maxlength="10">
      <div class="invalid-feedback"></div>
    </div>
  </div>
</div>
