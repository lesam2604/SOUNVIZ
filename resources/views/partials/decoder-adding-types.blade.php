<div class="col-12 col-lg-6 mb-3">
  <label for="type" class="form-label">Type d'ajout</label>
  <select id="type" class="form-select">
    <option value="one">Un décodeur</option>
    <option value="many">Plusieurs décodeurs</option>
    <option value="range">Intervalle</option>
  </select>
  <div class="invalid-feedback"></div>
</div>

<div class="col-12">
  <div class="row" id="oneBlock">
    <div class="col-12 col-lg-6 mb-3">
      <label for="decoderNumber" class="form-label">Numéro du décodeur</label>
      <input type="text" class="form-control" id="decoderNumber" placeholder="Numéro du décodeur" required
        minlength="14" maxlength="14">
      <div class="invalid-feedback"></div>
    </div>
  </div>

  <div class="row" id="manyBlock">
    <div class="col-12 col-lg-6 mb-3">
      <div class="input-group">
        <input type="text" class="form-control" id="decoderNumberMany" placeholder="Numéro du décodeur" required
          minlength="14" maxlength="14">
        <button class="btn btn-outline-primary" type="button" id="addDecoderNumber">
          <i class="fas fa-plus"></i> Ajouter
        </button>
        <div class="invalid-feedback"></div>
      </div>
    </div>

    <div class="col-12 mb-3">
      <table class="table table-bordered" id="tableDecoderNumbers">
        <thead>
          <tr>
            <th>Numéro</th>
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
      <label for="decoderNumberFrom" class="form-label">Numéro de début</label>
      <input type="text" class="form-control" id="decoderNumberFrom" placeholder="Numéro de début" required
        minlength="14" maxlength="14">
      <div class="invalid-feedback"></div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <label for="decoderNumberTo" class="form-label">Numéro de fin</label>
      <input type="text" class="form-control" id="decoderNumberTo" placeholder="Numéro de fin" required
        minlength="14" maxlength="14">
      <div class="invalid-feedback"></div>
    </div>
  </div>
</div>
