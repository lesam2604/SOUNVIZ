<h5 class="my-3">List des produits</h5>

<div class="col-12 col-lg-6 mb-3">
  <label for="invProductId" class="form-label">Produit</label>
  <select id="invProductId" class="form-select"></select>
  <div class="invalid-feedback"></div>
</div>

<div class="col-12 col-lg-6 mb-3">
  <label for="quantity" class="form-label">QuantitÃ©</label>
  <input type="number" class="form-control" id="quantity" placeholder="QuantitÃ©" required>
  <div class="invalid-feedback"></div>
</div>

<div class="text-center mt-2">
  <button type="button" class="btn btn-outline-primary" id="addProduct"><i class="fas fa-plus"></i> Ajouter le
    produit</button>
</div>

<table class="table table-bordered" id="tableProducts">
  <thead>
    <tr>
      <th>Code</th>
      <th>Nom</th>
      <th>CatÃ©gorie</th>
      <th>Prix unitaire</th>
      <th>QuantitÃ©</th>
      <th>CoÃ»t total</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>

  </tbody>
</table>
