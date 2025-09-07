window.datatableProducts = null;
window.selectedProduct = null;
window.selectedProductRow = null;

window.populateProductAdding = async function (products = null) {
  try {
    if (!products) {
      products = (
        await ajax({
          url: `${API_BASEURL}/inv-products/fetch-all`,
          type: 'GET',
        })
      ).data;
    }

    $('#invProductId').empty();

    for (const product of products) {
      $('#invProductId')
        .append(`<option value="${product.id}" data-code="${product.code}"
        data-category="${product.category.name}" data-unit_price="${product.unit_price}">${product.name}</option>`);
    }
  } catch ({ error }) {
    await Swal.fire(error.responseJSON.message, '', 'error');
  }
};

window.clearProductAdding = function () {
  $('#invProductId').val('').prop('disabled', false);
  $('#quantity').val('0');
  datatableProducts.clear().draw();
};

window.setProductAdding = function (products) {
  $('#invProductId').val('').change();
  $('#quantity').val('');

  datatableProducts.clear();

  for (const product of products) {
    datatableProducts.row.add({
      product_id: product.id,
      code: product.code,
      name: product.name,
      category_name: product.category.name,
      unit_price: product.pivot.unit_price,
      quantity: product.pivot.quantity,
      total_cost: product.pivot.unit_price * product.pivot.quantity,
    });
  }

  datatableProducts.draw();
};

window.fillProductAdding = function (data) {
  data.products = JSON.stringify(
    datatableProducts
      .rows()
      .data()
      .toArray()
      .map((row) => {
        return {
          id: row.product_id,
          quantity: row.quantity,
          unit_price: row.unit_price,
        };
      })
  );

  return data;
};

window.initDataTableProducts = function () {
  datatableProducts = $('#tableProducts')
    .on('click', '.edit', async function (e) {
      selectedProductRow = $(this).closest('tr');
      selectedProduct = datatableProducts.row(selectedProductRow).data();

      // Prompt pour éditer quantité et prix unitaire
      const { value: formValues } = await Swal.fire({
        title: `Modifier ${selectedProduct.name}`,
        html:
          `<div class="mb-2 text-start"><label class="form-label">Prix unitaire</label>`+
          `<input type="number" id="swalUnitPrice" class="form-control" value="${selectedProduct.unit_price}"></div>`+
          `<div class="mb-2 text-start"><label class="form-label">Quantité</label>`+
          `<input type="number" id="swalQuantity" class="form-control" value="${selectedProduct.quantity}"></div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Enregistrer',
        cancelButtonText: 'Annuler',
        preConfirm: () => {
          return {
            unit_price: parseFloat(document.getElementById('swalUnitPrice').value) || 0,
            quantity: parseInt(document.getElementById('swalQuantity').value) || 0,
          };
        }
      });

      if (!formValues) return;

      selectedProduct.unit_price = formValues.unit_price > 0 ? formValues.unit_price : selectedProduct.unit_price;
      selectedProduct.quantity = formValues.quantity > 0 ? formValues.quantity : selectedProduct.quantity;
      selectedProduct.total_cost = selectedProduct.unit_price * selectedProduct.quantity;
      datatableProducts.row(selectedProductRow).data(selectedProduct).draw(false);
      selectedProductRow = null;
      selectedProduct = null;
    })
    .on('click', '.remove', function (e) {
      datatableProducts.row($(this).closest('tr')).remove().draw(false);
    })
    .DataTable({
      columns: [
        {
          data: 'code',
        },
        {
          data: 'name',
        },
        {
          data: 'category_name',
        },
        {
          data: 'unit_price',
          render: (data, type, row) => {
            return formatAmount(data);
          },
        },
        {
          data: 'quantity',
        },
        {
          data: 'total_cost',
          render: (data, type, row) => {
            return formatAmount(data);
          },
        },
        {
          orderable: false,
          render: (data, type, row) => {
            return `
              <div class="d-flex">
                <button type="button" class="btn btn-sm btn-primary m-1 edit"><i class="fas fa-edit"></i> Éditer</button>
                <button type="button" class="btn btn-sm btn-danger m-1 remove"><i class="fas fa-minus"></i> Retirer</button>
              </div>
            `;
          },
        },
      ],
      order: [0, 'desc'],
      pageLength: -1,
      autoWidth: false,
    });

  $('#tableProducts').wrap('<div style="overflow-x: auto;"></div>');
};

window.initProductAdding = function () {
  initDataTableProducts();

  $('#addProduct').click(function (e) {
    let productOption = $('#invProductId').find('option:selected');
    let productId = parseInt(productOption.attr('value'));
    let quantity = parseInt($('#quantity').val());

    if (selectedProduct === null) {
      if (!productId) {
        Swal.fire('Veuillez sélectionner un produit', '', 'error');
        return;
      }

      let matchingRows = datatableProducts
        .rows()
        .data()
        .filter((row) => {
          return row.product_id === productId;
        });

      if (matchingRows.length > 0) {
        Swal.fire('Ce produit figure déjà sur la liste', '', 'error');
        return;
      }

      if (quantity <= 0) {
        Swal.fire("La quantité n'est pas valide", '', 'error');
        return;
      }

      let unitPrice = parseFloat($('#unitPrice').val());
      if (!(unitPrice > 0)) {
        unitPrice = parseFloat(productOption.data('unit_price'));
      }

      datatableProducts.row.add({
        product_id: productId,
        code: productOption.data('code'),
        name: productOption.text(),
        category_name: productOption.data('category'),
        unit_price: unitPrice,
        quantity: quantity,
        total_cost: unitPrice * quantity,
      });
    } else {
      let totalCost = selectedProduct.unit_price * quantity;

      selectedProduct.quantity = quantity;
      selectedProduct.total_cost = totalCost;
      datatableProducts.row(selectedProductRow).data(selectedProduct);
      selectedProductRow = null;
      selectedProduct = null;
    }

    datatableProducts.draw(false);

    $('#invProductId').val('').prop('disabled', false);
    $('#quantity').val('0');
    $('#unitPrice').val('');
  });
};
