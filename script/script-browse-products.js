// Functions for displaying products

function displayProducts(products) {

    var productTable = document.getElementById("product-table");
    var currentOption = document.getElementById("currency-select");
    
    if (products.length == 0) {
        productTable.innerHTML = `
            <div id="no-products">
                No product matches your search criteria! Please search again!
            </div>
        `;
        return;
    }

    productTable.innerHTML = ``;
    
    for (var i = 0; i < products.length; i++) {

        var product = products[i];

        productTable.innerHTML += `
            <div class="product-item">
                <img src="img/product/${product.image}">
                <div class="product-details">
                    <div class="product-name">
                        <label>${product.name}</label>
                    </div>
                    <div class="product-description">
                        <label>${product.description}</label>
                    </div>
                    <div class="product-price">
                        <label id="price-${product.id}">US$${product.price.toFixed(2)}</label>
                    </div>
                    <form method="POST" action="customer/browse-products.php">
                        <div class="quantity">
                            <button type="button" onclick="updateQuantity(${product.id}, -1)">-</button>
                            <input type="number"  id="quantity-${product.id}" name="quantity" value="0" class="quantity-input"
                                   min="0"  max="${product.quantity}"
                                   onchange="validateQuantity(${product.id})">
                            <button type="button" onclick="updateQuantity(${product.id}, 1)">+</button>
                        </div>
                        <div class="button-area">
                            <button type="submit" id="cart-btn-${product.id}" name="product_id" value="${product.id}" class="cart-btn" disabled>Add to Cart</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    for (var i = 0; i < products.length; i++) {

        let product = products[i];

        if (currentOption.value != "USD") {

            (async() => {

                converted = await convertAmount(product.price, currentOption.value);
                if (converted) {
                    document.getElementById(`price-${product.id}`).innerHTML += converted;
                }

            })();

        }
        
    }

}

// Initial display of the products

displayProducts(productList);

function updateQuantity(productId, change) {

    var product = findProduct(productId);

    if (product != null) {

        var quantityInput = document.getElementById("quantity-" + productId);
        var currentQuantity = parseInt(quantityInput.value) || 0;
        var revisedQuantity = change != null ? currentQuantity + change : currentQuantity;
        var addButton = document.getElementById("cart-btn-" + productId);

        if (revisedQuantity > product.quantity) {
            alert(`Sorry! Only ${product.quantity} units can be ordered for ${product.name}`);
            quantityInput.value = product.quantity;
            if (addButton) {
                addButton.disabled = false;
            }
            return;
        }

        if (revisedQuantity <= 0) {
            quantityInput.value = 0;
            revisedQuantity = 0;
        }

        if (addButton) {
            addButton.disabled = (revisedQuantity <= 0);
        }

        quantityInput.value = revisedQuantity;

    }

}

function validateQuantity(productId) {
    updateQuantity(productId, null);
}

// Functions for searching and sorting products

var currentList = productList;

function performSearch() {
    
    var searchString = document.getElementById("search");
    var searchWord = searchString.value.toLowerCase();
    var filteredProducts = [];
    
    if (searchWord == "" || searchWord == null) {

        filteredProducts = productList;

    } else {

        for (var i = 0; i < productList.length; i++) {

            var product = productList[i];
            if (product.name.toLowerCase().includes(searchWord) || product.description.toLowerCase().includes(searchWord)) {
                filteredProducts[filteredProducts.length] = product;
            }

        }

    }

    currentList = filteredProducts;
    sortProducts(filteredProducts);

    var productCount = document.getElementById("product-count");
    productCount.innerHTML = filteredProducts.length;

}

function resetSearch() {

    document.getElementById("search").value = "";
    document.getElementById("sort-select").value = "default";
    currentList = productList;
    displayProducts(productList);

    var productCount = document.getElementById("product-count");
    productCount.innerHTML = productList.length;

}

function sortProducts(list) {

    var sortSelected = document.getElementById("sort-select");
    var sortType = sortSelected ? sortSelected.value : "default";
    applySort(list, sortType);
    displayProducts(list);

}

function changeSort() {
    sortProducts(currentList);
}

function applySort(array, sortType) {

  switch (sortType) {
    case "price-low":
      array.sort((a, b) => a.price - b.price);
      break;
    case "price-high":
      array.sort((a, b) => b.price - a.price);
      break;
    default:
      array.sort((a, b) => a.id - b.id);
      break;
  }

}

// Functions for changing currency

function changeCurrencyDisplay() {

    var currentOption = document.getElementById("currency-select");

    if (currentOption.value != "USD") {
        alert("Foreign curreny amounts are for reference only! The actual orders will be paid in US$!");
    }

    displayProducts(currentList);

}
