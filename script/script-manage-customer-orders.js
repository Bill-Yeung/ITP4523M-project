var originalOrders = orderList.slice()
var currentOrders = orderList || [];
var filteredOrders = [];

var sortState = {
    primary: "",
    secondary: "",
    tertiary: "",
    directions: { primary: "ASC", secondary: "ASC", tertiary: "ASC" }
};

// Functions for displaying orders

function displayOrders(orders) {

    var tbody = document.getElementById("orders-body");
    
    if (orders.length == 0) {
        tbody.innerHTML = `
            <tr><td id="no-match" colspan="10">No orders found</td></tr>
        `;
        return;
    }

    tbody.innerHTML = ``;
    
    for (var i = 0; i < orders.length; i++) {

        var order = orders[i];
        var statusText = ["Rejected", "Open", "Processing", "Approved", "Pending delivery", "Completed"];
        var productionStatusText = order.production_status ? ucfirst(order.production_status) : "N/A";
        var orderCancel = "";
        if (order.cancel == 0) {
            orderCancel = "Not Requested";
        } else if (order.cancel == 1) {
            orderCancel = "Requested";
        } else if (order.cancel == 2) {
            orderCancel = "Approved";
        } else {
            orderCancel = "Request but not approved";
        }
        
        tbody.innerHTML += `
            <tr class="order-row">
                <td class="col1">${order.order_id}</td>
                <td class="col2">${formatDate(order.order_date)}</td>
                <td class="col3">
                    <div class="product-info">
                        <img src="img/product/${order.product_image}">
                        <div>
                            <div class="product-name">${order.product_name}</div>
                            <div class="product-id">ID: ${order.product_id}</div>
                        </div>
                    </div>
                </td>
                <td class="col4">${order.quantity}</td>
                <td class="col5" id="price-${order.order_id}">US$${parseFloat(order.total_cost).toFixed(2)}</td>
                <td class="col6">${formatDate(order.delivery_date)}</td>
                <td class="col7">${statusText[order.status]}</td>
                <td class="col8"><span class="status-${order.production_status}">${productionStatusText}</span></td>
                <td class="col9">${orderCancel}</td>
                <td class="col10">
                    <button type="button" class="action-btn view-btn" onclick="showOrderModal(${order.order_id})">View</button>
                    ${order.status == 0 ? 
                        '<button type="button" class="action-btn edit-btn disabled" disabled title="Cannot edit rejected orders">Edit</button>' :
                        `<button type="button" class="action-btn invoice-btn" onclick="window.location.href='staff/order/edit-order.php?oid=${order.order_id}'">Edit</button>`
                    }
                </td>
            </tr>
        `;

    }

}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateString) {

    if (!dateString || dateString == null || dateString == "") {
        return "Not Set";
    }
    
    if (dateString.includes(" ")) {
        return dateString.split(" ")[0];
    }

    return dateString;

}

function updateOrderCount(count) {
    document.getElementById("order-count").innerHTML = count;
}

// Functions for sorting orders

function applyOrdersDisplay() {

    filteredOrders = filterOrdersByStatus(currentOrders);
    var sortedOrders = sortOrders(filteredOrders);
    displayOrders(sortedOrders);
    updateOrderCount(sortedOrders.length);

}

function filterOrdersByStatus(orders) {

    var status = document.getElementById("status-filter").value;
    
    if (status == "all") {
        return orders;
    }
    
    var result = [];
    for (var i = 0; i < orders.length; i++) {
        if (orders[i].status == status) {
            result.push(orders[i]);
        }
    }

    return result;

}

function sortOrders(orders) {

    if (!sortState.primary) {
        return orders;
    }
    
    orders.sort(function(a, b) {

        var result = compareOrders(a, b, sortState.primary, sortState.directions.primary);
        if (result != 0) {
            return result;
        }
        
        if (sortState.secondary) {
            result = compareOrders(a, b, sortState.secondary, sortState.directions.secondary);
            if (result != 0) {
                return result;
            }
        }
        
        if (sortState.tertiary) {
            result = compareOrders(a, b, sortState.tertiary, sortState.directions.tertiary);
        }
        
        return result;

    });
    
    return orders;
}

function compareOrders(a, b, column, direction) {

    var valueA = getOrderValue(a, column);
    var valueB = getOrderValue(b, column);
    var result = 0;
    
    if (column == "delivery_date") {

        var dateA = Date.parse(valueA) || 0;
        var dateB = Date.parse(valueB) || 0;
        result = dateA - dateB;
        
    } else if (column == "order_id" || column == "status") {

        result = parseInt(valueA) - parseInt(valueB);

    } else if (column == "total_cost") {

        result = parseFloat(valueA) - parseFloat(valueB);

    } else if (column == "order_date") {

        var dateOnlyA = valueA.split(" ")[0];
        var dateOnlyB = valueB.split(" ")[0];
        result = new Date(dateOnlyA) - new Date(dateOnlyB);

    }
    
    return direction == "ASC" ? result : -result;

}

function getOrderValue(order, column) {
    switch (column) {
        case "order_id": 
            return order.order_id;
        case "order_date": 
            return order.order_date;
        case "total_cost": 
            return order.total_cost;
        case "delivery_date": 
            return order.delivery_date;
        case "status": 
            return order.status;
        default: 
            return "";
    }
}

// Functions for change in sorting orders

function sortChange() {
  
    var primary = document.getElementById("primary-sort").value;
    var secondary = document.getElementById("secondary-sort").value;
    var tertiary = document.getElementById("tertiary-sort").value;
    
    sortState.primary = primary;
    sortState.secondary = (secondary != primary) ? secondary : "";
    sortState.tertiary = (tertiary != primary && tertiary != secondary) ? tertiary : "";
    
    updateHeaders();
    applyOrdersDisplay();

}

function updateHeaders() {
  
    var headers = document.querySelectorAll(".table-header");
    var columnNames = ["order_id", "order_date", "product_name", "quantity", "total_cost", "delivery_date", "status"];
    
    for (var i = 0; i < columnNames.length; i++) {

        var header = headers[i];
        header.classList.remove("sorted");
        header.style.cursor = "default";
        header.onclick = null;
      
        var indicators = header.querySelector(".sort-indicators");
        indicators.innerHTML = "";
      
        var columnName = columnNames[i];
        let sortLevel = "";
        var direction = "ASC";
        
        if (sortState.primary == columnName) {

            sortLevel = "primary";
            direction = sortState.directions.primary;

        } else if (sortState.secondary == columnName) {

            sortLevel = "secondary";
            direction = sortState.directions.secondary;

        } else if (sortState.tertiary == columnName) {

            sortLevel = "tertiary";
            direction = sortState.directions.tertiary;

        }
        
        if (sortLevel) {

            header.classList.add("sorted");
            header.style.cursor = "pointer";
            header.onclick = function() { changeDirection(sortLevel); };
            
            var arrow = direction == "ASC" ? "▲" : "▼";
            indicators.innerHTML = `<span class="sort-arrow">${arrow}</span>`;

        }

    }

}

function changeDirection(level) {

    sortState.directions[level] = sortState.directions[level] == "ASC" ? "DESC" : "ASC";
    updateHeaders();
    applyOrdersDisplay();

}

function clearAllSort() {

    document.getElementById("primary-sort").value = "";
    document.getElementById("secondary-sort").value = "";
    document.getElementById("tertiary-sort").value = "";
    
    sortState.primary = "";
    sortState.secondary = "";
    sortState.tertiary = "";
    sortState.directions = { primary: "ASC", secondary: "ASC", tertiary: "ASC" };
    
    updateHeaders();
    var filtered = filterOrdersByStatus(originalOrders);
    displayOrders(filtered)
    updateOrderCount(filtered.length);

}

// Initial display

applyOrdersDisplay();

// Functions for displaying view

function showOrderModal(orderId) {

    var order = findOrder(orderId);

    if (!order) {
        alert("Order not found!");
        return;
    }

    var statusText = ["Rejected", "Open", "Processing", "Approved", "Pending delivery", "Completed"];
    var productionStatusText = order.production_status ? ucfirst(order.production_status) : "N/A";
    var orderCancel = "";
    if (order.cancel == 0) {
        orderCancel = "Not Requested";
    } else if (order.cancel == 1) {
        orderCancel = "Requested";
    } else if (order.cancel == 2) {
        orderCancel = "Approved";
    } else {
        orderCancel = "Request but not approved";
    }

    var usedMaterials = findActualMaterial(order.order_id) || [];

    var isProductionFinished = order.production_status == 'finished';
    var materialTableTitle = isProductionFinished ? "Used Materials" : "Reserved Materials";

    var noMaterialsMessage = order.status == 0 ? 
        "No materials data (order was rejected/cancelled)" : 
        "No materials data available";

    var modalContent = document.getElementById("modal-inner");

        modalContent.innerHTML = `

            <div id="item-details">
            
                <div id="item-image-section">
                    <img src="img/product/${findProduct(order.product_id).image}" id="modal-image">
                </div>
                
                <div id="item-info-section">
                    <table id="item-info-table">
                        <tr>
                            <td><strong>Order ID:</strong></td>
                            <td>${order.order_id}</td>
                        </tr>
                        <tr>
                            <td><strong>Order Date:</strong></td>
                            <td>${order.order_date}</td>
                        </tr>
                        <tr>
                            <td><strong>Product ID:</strong></td>
                            <td>${order.product_id}</td>
                        </tr>
                        <tr>
                            <td><strong>Product Name:</strong></td>
                            <td>${findProduct(order.product_id).name}</td>
                        </tr>
                        <tr>
                            <td><strong>Order Quantity:</strong></td>
                            <td>${order.quantity}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Order Amount:</strong></td>
                            <td>US$${order.total_cost.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td><strong>Delivery Date:</strong></td>
                            <td>${order.delivery_date ?? "Not Set"}</td>
                        </tr>
                        <tr>
                            <td><strong>Order Status:</strong></td>
                            <td>${statusText[order.status]}</td>
                        </tr>
                        <tr>
                            <td><strong>Production Status:</strong></td>
                            <td><span class="status-${order.production_status}">${productionStatusText}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Order Cancel Request:</strong></td>
                            <td>${orderCancel}</td>
                        </tr>
                    </table>
                </div>

                <div id="customer-info-section">
                    <h3>Customer Details</h3>
                    <table id="customer-info-table">
                        <tr>
                            <td><strong>Customer Name:</strong></td>
                            <td>${findCustomer(order.customer).name}</td>
                        </tr>
                        <tr>
                            <td><strong>Contact Number:</strong></td>
                            <td>${findCustomer(order.customer).contact}</td>
                        </tr>
                        <tr>
                            <td><strong>Delivery Address:</strong></td>
                            <td>${findCustomer(order.customer).address}</td>
                        </tr>
                    </table>
                </div>

                <div id="materials-info-section">
                    <h3>${materialTableTitle}</h3>
                    <table id="materials-info-table">
                        <thead>
                            <tr>
                                <th>Material ID</th>
                                <th>Material Name</th>
                                <th>Quantity ${isProductionFinished ? "Used" : "Reserved"}</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${usedMaterials.length > 0 ? 
                                usedMaterials.map(material => `
                                    <tr>
                                        <td>${material.material_id}</td>
                                        <td>${findMaterial(material.material_id).name}</td>
                                        <td>${material.reserved_qty}</td>
                                        <td>${findMaterial(material.material_id).unit}</td>
                                    </tr>
                                `).join('') :
                                '<tr><td colspan="4">No materials data available</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>
                <div id="general-materials-section">
                    <h3>General Material Status</h3>
                    <table id="general-materials-table">
                        <thead>
                            <tr>
                                <th>Material ID</th>
                                <th>Material Name</th>
                                <th>Material Unit</th>
                                <th>Physical Quantity</th>
                                <th>Available Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${materialList.length > 0 ? 
                                materialList.map(material => {
                                    var availableQty = material.quantity - material.reserved;
                                    var isLowStock = availableQty <= material.reorder;
                                    var statusClass = isLowStock ? "low-stock" : "adequate-stock";
                                    var statusText = isLowStock ? "LOW STOCK" : "OK";
                                    
                                    return `
                                        <tr class="${isLowStock ? 'low-stock-row' : ''}">
                                            <td>${material.id}</td>
                                            <td>${material.name}</td>
                                            <td>${material.unit}</td>
                                            <td>${material.quantity}</td>
                                            <td>${availableQty}</td>
                                            <td class="${statusClass}">${statusText}</td>
                                        </tr>
                                    `;
                                }).join('') :
                                '<tr><td colspan="6">No materials data available</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>
                
                ${order.cancel != 2 ? `
                <div id="action-buttons-modal">
                    <button type="button" id="btn-edit" onclick="window.location.href='staff/order/edit-order.php?oid=${order.order_id}'">Edit Order</button>
                    <!--<button type="button" id="btn-delete" onclick="cancelOrder(${order.order_id})">Delete Order</button>-->
                </div>
                ` : `
                <div id="action-buttons-modal">
                    <p id="terminated-message">This order has been cancelled and cannot be modified.</p>
                </div>
                `}
                
        </div>

        `;

    document.getElementById("orderModal").style.display = "block";

}

function closeOrderModal() {
    document.getElementById("orderModal").style.display = "none";
}

// Functions for cancel order

function cancelOrder(orderId) {

    if (confirm("Are you sure you want to cancel this order? This action cannot be undone.")) {

        var form = document.createElement("form");
        form.method = "POST";
        form.action = `staff/order/delete-order.php?oid=${orderId}`;
        document.body.appendChild(form);
        form.submit();

    }

}

// Functions for edit products

function updateProductCost() {

    var productSelect = document.getElementById("product-select");
    var productId = productSelect.value;
    var unitCost = document.getElementById("unit-cost");
    
    if (productId) {
        var product = findProduct(productId);
        if (product) {
            unitCost.value = product.price.toFixed(2);
            updateMaterialQuantities();
        }
    } else {
        unitCost.value = "";
    }

    calculateTotal();

}

function calculateTotal() {

    var quantity = parseFloat(document.getElementById("oqty").value);
    var unitCost = parseFloat(document.getElementById("unit-cost").value);
    var total = quantity * unitCost;
    document.getElementById("ocost").value = total.toFixed(2);

    updateMaterialQuantities();

}

function updateMaterialQuantities() {

    var productSelect = document.getElementById("product-select");
    var productId = null;
    
    if (productSelect) {
        productId = productSelect.value;
    } else {
        var hiddenProductId = document.getElementById("current-product-id");
        if (hiddenProductId) {
            productId = hiddenProductId.value;
        }
    }

    var orderQty = parseInt(document.getElementById("oqty").value);
    
    if (productId && orderQty && productMatData[productId]) {

        productMatData[productId].forEach(material => {
            var mid = material.mid;
            var suggestedQty = material.pmqty;
            var requiredQty = suggestedQty * orderQty;
            
            var requiredQtyElement = document.getElementById("required-qty-" + mid);
            if (requiredQtyElement) {
                requiredQtyElement.innerHTML = requiredQty;
            }
            
            var reservedQtyElement = document.getElementById("reserved-qty-" + mid);
            if (reservedQtyElement) {
                reservedQtyElement.value = requiredQty;
            }

        });

    }

}

function updateCustomerInfo() {

    var customerSelect = document.getElementById("customer-select");
    var selectedCustomerId = customerSelect.value;
    
    if (selectedCustomerId) {
        var customer = findCustomer(selectedCustomerId);
        if (customer) {
            document.getElementById("customer-company-name").value = customer.company;
            document.getElementById("customer-email").value = customer.email;
            document.getElementById("customer-tel").value = customer.contact;
            document.getElementById("customer-addr").value = customer.address;
        }
    } else {
        document.getElementById("customer-copmpany-name").value = "";
        document.getElementById("customer-email").value = "";
        document.getElementById("customer-tel").value = "";
        document.getElementById("customer-addr").value = "";
    }
}

function changeMaterialQuantities() {

    var productSelect = document.getElementById("product-select");
    var productId = null;
    
    if (productSelect) {
        productId = productSelect.value;
    } else {
        var hiddenProductId = document.getElementById("current-product-id");
        if (hiddenProductId) {
            productId = hiddenProductId.value;
        }
    }
    
    var orderQty = parseInt(document.getElementById("oqty").value);
    var tbody = document.getElementById("material-usage-tbody");
    var materialSection = document.getElementById("material-section");
    var noMaterialsMessage = document.getElementById("no-materials-message");
    tbody.innerHTML = "";
    
    if (productId && orderQty && productMatData[productId]) {

        if (materialSection) {
            materialSection.style.display = "block";
        }
        if (noMaterialsMessage) {
            noMaterialsMessage.style.display = "none";
        }
        
        productMatData[productId].forEach(function(material) {

            var requiredQty = material.pmqty * orderQty;
            var row = document.createElement("tr");

            row.innerHTML = `
                <td>${material.mid}</td>
                <td>${material.mname}</td>
                <td>${material.munit}</td>
                <td id="required-qty-${material.mid}" class="required-qty">${requiredQty}</td>
                <td>
                    <input type="number" 
                           id="reserved-qty-${material.mid}"
                           name="material_qty[${material.mid}]" 
                           value="${requiredQty}" 
                           min="0" 
                           step="1"
                           class="reserved-qty-input">
                </td>
            `;
            tbody.appendChild(row);
        });
    } else {

        if (materialSection) {
            materialSection.style.display = "none";
        }
        if (noMaterialsMessage) {
            noMaterialsMessage.style.display = "block";
        }

    }
}