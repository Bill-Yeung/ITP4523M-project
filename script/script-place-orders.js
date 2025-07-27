
// Functions for changing currency display

function changeCurrencyDisplay() {

    var currentOption = document.getElementById("currency-select");
    
    if (currentOption.value != "USD") {
        document.getElementById("warning-label").style.display = "inline";
        alert("Foreign currency amounts are for reference only! The actual orders will be paid in US$!");
    } else {
        document.getElementById("warning-label").style.display = "none";
    }
    
    displayCartPrices();

}

function displayCartPrices() {

    var currentOption = document.getElementById("currency-select");

    for (var i = 0; i < productList.length; i++) {

        var product = productList[i];
        var priceElement = document.getElementById("price-" + product.id);
        var totalElement = document.getElementById("cart-total-" + product.id);
        var quantityElement = document.getElementById("quantity-" + product.id);
        
        if (!priceElement || !totalElement || !quantityElement) {
            continue;
        }
        
        var quantity = parseInt(quantityElement.value);
        
        priceElement.innerHTML = "US$" + product.price.toFixed(2);
        totalElement.innerHTML = "US$" + (product.price * quantity).toFixed(2);
        
        if (currentOption.value != "USD") {

            let currentProduct = product;
            let currentQuantity = quantity;
            let currentPriceElement = priceElement;
            let currentTotalElement = totalElement;

            (async() => {
                var converted = await convertAmount(currentProduct.price, currentOption.value);
                if (converted) {
                    currentPriceElement.innerHTML += " " + converted;
                }
            })();
            
            (async() => {
                var convertedTotal = await convertAmount(currentProduct.price * currentQuantity, currentOption.value);
                if (convertedTotal) {
                    currentTotalElement.innerHTML += " " + convertedTotal;
                }
            })();

        }

    }
    
    var orderTotalElement = document.getElementById("cart-total-amount");
    orderTotalElement.innerHTML = "US$" + totalAmount.toFixed(2);
    
    if (currentOption.value != "USD") {
        (async() => {
            var convertedOrderTotal = await convertAmount(totalAmount, currentOption.value);
            if (convertedOrderTotal) {
                orderTotalElement.innerHTML += " " + convertedOrderTotal;
            }
        })();
    }

}

function updateCart(productId, change) {

    var product = findProduct(productId);

    if (product != null) {

        var quantityInput = document.getElementById("quantity-" + productId);
        var currentQuantity = parseInt(quantityInput.value) || 0;
        var revisedQuantity = change != null ? currentQuantity + change : currentQuantity;

        if (revisedQuantity > product.quantity) {
            alert(`Sorry! Only ${product.quantity} units are available for ${product.name}`);
            revisedQuantity = product.quantity;
        }

        if (revisedQuantity < 1) {
            alert("Please remove the product from the cart if you do not need it already!");
            revisedQuantity = 1;
        }

        $(document).ready(function () {
            (function () {
                $.ajax({
                    type: 'POST',
                    url: 'customer/update-cart-quantity.php',
                    dataType: 'json',
                    data: {
                        productId: productId,
                        revisedQuantity: revisedQuantity
                    },
                    success: function(result) {
                        if (result.success) {
                            quantityInput.value = revisedQuantity;
                            totalAmount = 0
                            for (var prodId in result.cart) {
                                totalAmount += findProduct(prodId).price * result.cart[prodId];
                            }
                            displayCartPrices();
                        }
                    },
                    error: function(err) {
                        console.log("error" + err);
                    }
                });
            }) ();
        });

    }

}

function validateCart(productId) {
    updateCart(productId, null);
}

function confirmOrder() {

    var telephone = document.getElementById("customer-telephone");
    var address = document.getElementById("customer-address");

    if (telephone.innerHTML.trim() == "") {
        alert("Please enter your contact number before placing the order!")
        return false;
    }

    if (address.innerHTML.trim() == "") {
        alert("Please enter your address before placing the order!")
        return false;
    }

    var input = window.confirm("Are you sure that you want to place this order?");
    if (input) {

        var password = window.prompt("Please enter your password to confirm the order!", "");

        var form = document.getElementById("confirm-order");
        form.value = password;

        return true;

    } else {
        return false;
    }

}

// Initialize cart prices

displayCartPrices();