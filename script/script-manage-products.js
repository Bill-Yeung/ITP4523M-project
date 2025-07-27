// Functions for displaying products

function displayProducts(products) {

    var productTable = document.getElementById("products-body");

    productTable.innerHTML = ``;
    
    for (var i = 0; i < products.length; i++) {

        var product = products[i];

        productTable.innerHTML += `
            <tr class="item-row">
                <td class="col1">${product.id}</td>
                <td class="col2 item-name">${product.name}</td>
                <td class="col3"><img src="img/product/${product.image}" class="item-image"></td>
                <td class="col4">US$${product.price.toFixed(2)}</td>
                <td class="col6">${product.availability == 1 ? "Available" : "Deactivated"}</td>
                <td class="col7">
                    <button type="button" class="action-btn view-btn" onclick="showProductModal(${product.id})">View</button>
                    ${product.availability == 1 ? `<button type="button" class="action-btn edit-btn" onclick="window.location.href='staff/product/edit-product.php?pid=${product.id}'">Edit</button>` : `` }
                    ${product.availability == 1 ? `<button type="button" class="action-btn delete-btn" onclick="deleteProduct(${product.id})">Delete</button>` : `` }
                </td>
            </tr>
        `;
    }

}

function applyProductFilter() {

    var filter = document.getElementById("status-filter").value;
    var searchTerm = document.getElementById("search").value.toLowerCase();
    
    var filteredProducts = productList.filter(function(product) {
        var matchesSearch = product.name.toLowerCase().includes(searchTerm);
        var matchesFilter = true;
        
        if (filter == "active") {
            matchesFilter = product.availability == 1;
        } else if (filter == "inactive") {
            matchesFilter = product.availability == 0;
        }
        
        return matchesSearch && matchesFilter;
    });
    
    displayProducts(filteredProducts);
    updateProductCount(filteredProducts.length);

}

// Initial display of the products

applyProductFilter();

// Functions for searching and sorting products

var currentList = productList;

function performSearch() {
    
    applyProductFilter();

}

function resetSearch() {

    document.getElementById("search").value = "";
    document.getElementById("status-filter").value = "all";
    displayProducts(productList);
    updateProductCount(productList.length);

}

function updateProductCount(count) {
    document.getElementById("item-count").innerHTML = count;
}

// Functions for displaying view

function showProductModal(productId) {

    var product = findProduct(productId);

    if (!product) {
        alert("Product not found!");
        return;
    }

    var modalContent = document.getElementById("modal-inner");

        modalContent.innerHTML = `

            <div id="item-details">
            
                <div id="item-image-section">
                    <img src="img/product/${product.image}" id="modal-image">
                </div>
                
                <div id="item-info-section">
                    <table id="item-info-table">
                        <tr>
                            <td><strong>Product ID:</strong></td>
                            <td>${product.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Product Name:</strong></td>
                            <td>${product.name}</td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>${product.description || "No description available"}</td>
                        </tr>
                        <tr>
                            <td><strong>Unit Price:</strong></td>
                            <td>US$${product.price.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td><strong>Availability:</strong></td>
                            <td>${product.availability == 1 ? "Available" : "Deactivated"}</td>
                        </tr>
                    </table>
                </div>
                
                ${product.availability == 1 ? `
                <div id="action-buttons-modal">
                    <button type="button" id="btn-edit" onclick="window.location.href='staff/product/edit-product.php?pid=${product.id}'">Edit Product</button>
                    <button type="button" id="btn-delete" onclick="deleteProduct(${product.id})">Delete Product</button>
                </div>
                ` : `
                <div id="action-buttons-modal">
                    <p id="terminated-message">This product is deactivated and cannot be modified.</p>
                </div>
                `}
                
        </div>

        `;

    document.getElementById("itemModal").style.display = "block";

}

function closeProductModal() {
    document.getElementById("itemModal").style.display = "none";
}

function deleteProduct(productId) {
    if (confirm(`Are you sure you want to delete "${findProduct(productId).name}"? This action cannot be undone.`)) {
        window.location.href = `staff/product/delete-product.php?pid=${productId}`;
    }
}

