function applyProductionDisplay() {

    var statusFilter = document.getElementById('status-filter').value;
    var rows = document.querySelectorAll('#production-body .order-row');
    let visibleCount = 0;

    rows.forEach(row => {
        var proid = parseInt(row.querySelector('.col1').innerHTML);
        var production = productionList.find(p => p.proid == proid);
        let shouldShow = false;

        switch (statusFilter) {
            case 'all':
                shouldShow = true;
                break;
            case 'pending':
                shouldShow = production.order_status > 0 && production.order_status < 3;
                break;
            case 'approved':
                shouldShow = production.order_status == 3;
                break;
            case 'approved-open':
                shouldShow = production.order_status == 3 && production.pstatus == 'open';
                break;
            case 'approved-started':
                shouldShow = production.order_status == 3 && production.pstatus == 'started';
                break;
            case 'approved-finished':
                shouldShow = production.order_status == 3 && production.pstatus == 'finished';
                break;
            case 'canceled':
                shouldShow = production.order_status == 0 || production.pstatus == 'cancel';
                break;
        }

        if (shouldShow) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('order-count').innerHTML = visibleCount;

}

applyProductionDisplay();

function filterProductionByStatus(productions) {

    var status = document.getElementById("status-filter").value;
    
    if (status == "all") {
        return productions;
    }
    
    var result = [];
    for (var i = 0; i < productions.length; i++) {
        if (productions[i].pstatus == status) {
            result.push(productions[i]);
        }
    }

    return result;
}

function updateProductInfo() {

    var select = document.getElementById("pid");
    var selectedProductId = parseInt(select.value);
    
    if (selectedProductId) {

        var product = findProduct(selectedProductId);
        
        if (product) {

            document.getElementById("product-info-section").style.display = "block";
            document.getElementById("product-image").src = "img/product/" + product.image;
            document.getElementById("product-name").textContent = product.name;
            document.getElementById("product-price").textContent = "US$" + product.price.toFixed(2);
            document.getElementById("current-stock").textContent = product.quantity;
            
            updateMaterialDisplay(selectedProductId);
            updateMaterialQuantities();
        }
    } else {
        document.getElementById("product-info-section").style.display = "none";
        document.getElementById("material-section").style.display = "none";
    }
}

var currentProductMaterials = [];

function updateMaterialDisplay(productId) {

    var materials = prodmatList[productId];
    var tbody = document.getElementById("material-usage-tbody");
    
    currentProductMaterials = materials;
    
    if (materials.length > 0) {

        document.getElementById("material-section").style.display = "block";
        document.getElementById("no-materials-message").style.display = "none";
        
        tbody.innerHTML = "";
        materials.forEach(function(material, index) {
            var materialInfo = findMaterial(material.id);
            if (materialInfo) {
                var row = document.createElement("tr");
                row.innerHTML = `
                    <td>${material.id}</td>
                    <td>${materialInfo.name}</td>
                    <td>${materialInfo.unit}</td>
                    <td>${material.qty}</td>
                    <td>0</td>
                    <td>${materialInfo.quantity - materialInfo.reserved}</td>
                    <td>-</td>
                `;
                tbody.appendChild(row);
            }
        });

    } else {

        document.getElementById("material-section").style.display = "block";
        document.getElementById("no-materials-message").style.display = "block";
        tbody.innerHTML = "";
        currentProductMaterials = [];

    }

}

function updateMaterialQuantities() {

    var quantity = parseInt(document.getElementById("pqty").value) || 0;
    var selectedProductId = parseInt(document.getElementById("pid").value);
    
    if (selectedProductId) {
        var product = findProduct(selectedProductId);

        if (product) {

            var currentStock = parseInt(product.quantity);
            
            document.getElementById("display-quantity").innerHTML = quantity;
            document.getElementById("new-stock").innerHTML = currentStock + quantity;
            
            var tbody = document.getElementById("material-usage-tbody");
            var rows = tbody.getElementsByTagName("tr");
            
            for (var i = 0; i < rows.length && i < currentProductMaterials.length; i++) {

                var material = currentProductMaterials[i];
                var materialInfo = findMaterial(material.id);
                
                if (materialInfo) {

                    var perUnit = material.qty;
                    var totalRequired = perUnit * quantity;
                    var availableStock = materialInfo.quantity - materialInfo.reserved;
                    rows[i].cells[4].innerHTML = totalRequired;
                    
                    var statusCell = rows[i].cells[6];
                    if (totalRequired <= availableStock) {
                        statusCell.textContent = "OK";
                        statusCell.className = "material-status status-ok";
                    } else {
                        statusCell.textContent = "INSUFFICIENT";
                        statusCell.className = "material-status status-insufficient";
                    }
                }

            }

        }

    }

}