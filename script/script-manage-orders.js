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
    var currentOption = document.getElementById("currency-select");
    
    if (orders.length == 0) {
        tbody.innerHTML = `
            <tr><td id="no-match" colspan="8">No orders found</td></tr>
        `;
        return;
    }

    tbody.innerHTML = ``;
    
    for (var i = 0; i < orders.length; i++) {

        var order = orders[i];
        var statusText = ["Rejected", "Open", "Processing", "Approved", "Pending delivery", "Completed"];
        
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
                <td class="col8">
                    <button type="button" class="action-btn view-btn" onclick="showOrderModal(${order.order_id})">View</button>
                    ${generateInvoiceButton(order)}
                    ${generateCancelButton(order)}
                </td>
            </tr>
        `;

    }

    for (var i = 0; i < orders.length; i++) {

        let order = orders[i];

        if (currentOption.value != "USD") {

            (async() => {

                converted = await convertAmount(order.total_cost, currentOption.value);
                if (converted) {
                    document.getElementById(`price-${order.order_id}`).innerHTML += converted;
                }

            })();

        }
        
    }

}

function generateInvoiceButton(order) {

    if (order.status == 0 || order.cancel == 1) {
        return "";
    }
        
    return `<button type="button" class="action-btn invoice-btn" onclick="generateInvoice(${order.order_id})">Invoice</button>`;

}

function generateCancelButton(order) {

    if (order.status == 0 || order.status == 5) {
        return "";
    }
    
    if (order.cancel == 1) {
        return '<button type="button" class="action-btn cancel-request">Cancel Requested</button>';
    } else if (order.cancel == 2) {
        return '<button type="button" class="action-btn cancel-approved">Cancel Approved</button>';
    } else if (order.cancel == 3) {
        return '<button type="button" class="action-btn cancel-denied">Cancel Denied</button>';
    } else {
        return `<button type="button" class="action-btn cancel-btn" onclick="cancelOrder(${order.order_id})">Cancel</button>`;
    }
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

function formatCurrency(amount) {

    var currency = document.getElementById("currency-select").value;
    
    if (currency == "USD") {

        return `US$${parseFloat(amount).toFixed(2)}`;

    } else {

        return (async() => {

            converted = await convertAmount(parseFloat(amount), currency);
            if (converted) {
                return `US$${parseFloat(amount).toFixed(2)}${converted}`;
            } else {
                return `US$${parseFloat(amount).toFixed(2)}`;
            }

        })();

    }

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

// Functions for displaying different currencies

function changeCurrencyDisplay() {

    var currentOption = document.getElementById("currency-select");

    if (currentOption.value != "USD") {
        alert("Foreign curreny amounts are for reference only! The actual orders will be paid in US$!");
    }

    applyOrdersDisplay();

}

// Initial display

applyOrdersDisplay();

// Functions for action buttons

function showOrderModal(orderId) {

    var order = findOrder(orderId);

    if (!order) {
        alert("Order not found!");
        return;
    }
    
    var modalInner = document.getElementById("modal-inner");
    var statusText = ["Rejected", "Open", "Processing", "Approved", "Pending delivery", "Completed"];
    var cancelStatusText = ["Not Requested", "Requested", "Cancel Approved", "Cancel Denied"];
    
    modalInner.innerHTML = `
        <div class="order-details">

            <div id="item-image-section">
                <img src="img/product/${order.product_image}" id="modal-image">
            </div>

            <h3>Order Information</h3>
            <div id="item-info-section">
                <table id="item-info-table">
                    <tr>
                        <td><strong>Order ID:</strong></td>
                        <td>${order.order_id}</td>
                    </tr>
                    <tr>
                        <td><strong>Order Date:</strong></td>
                        <td>${formatDate(order.order_date)}</td>
                    </tr>
                    <tr>
                        <td><strong>Delivery Date:</strong></td>
                        <td>${formatDate(order.delivery_date)}</td>
                    </tr>
                    <tr>
                        <td><strong>Order Status:</strong></td>
                        <td>${statusText[order.status]}</td>
                    </tr>
                    <tr>
                        <td><strong>Cancel Status:</strong></td>
                        <td>${cancelStatusText[order.cancel]}</td>
                    </tr>
                </table>
            </div>

            <h3>Product Information</h3>
            <div id="item-info-section">
                <table id="item-info-table">
                    <tr>
                        <td><strong>Product Name:</strong></td>
                        <td>${order.product_name}</td>
                    </tr>
                    <tr>
                        <td><strong>Product ID:</strong></td>
                        <td>${order.product_id}</td>
                    </tr>
                    <tr>
                        <td><strong>Description:</strong></td>
                        <td>${order.product_desc}</td>
                    </tr>
                    <tr>
                        <td><strong>Quantity:</strong></td>
                        <td>${order.quantity}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Cost:</strong></td>
                        <td>US$${parseFloat(order.total_cost).toFixed(2)}</td>
                    </tr>
                </table>
            </div>

        </div>
    `;
    
    document.getElementById("orderModal").style.display = "block";
}

function closeOrderModal() {
    document.getElementById("orderModal").style.display = "none";
}

function generateInvoice(orderId) {
    window.open(`customer/generate-invoice.php?order_id=${orderId}&preview=1`, '_blank');
}

function cancelOrder(orderId) {

    if (confirm("Are you sure you want to cancel this order? This action cannot be undone.")) {

        var form = document.createElement("form");
        form.method = "POST";
        form.action = "customer/manage-orders.php";
        
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "cancel_order";
        input.value = orderId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();

    }

}

