// Functions for locating the information

function findProduct(productId) {
    for (var i = 0; i < productList.length; i++) {
        if (productList[i].id == productId) {
            return productList[i];
        }
    }
    return null;
}

function findMaterial(materialId) {
    for (var i = 0; i < materialList.length; i++) {
        if (materialList[i].id == materialId) {
            return materialList[i];
        }
    }
    return null;
}

function findOrder(orderId) {
    for (var i = 0; i < orderList.length; i++) {
        if (orderList[i].order_id == orderId) {
            return orderList[i];
        }
    }
    return null;
}

function findCustomer(customerId) {
    for (var i = 0; i < customerList.length; i++) {
        if (customerList[i].id == customerId) {
            return customerList[i];
        }
    }
    return null;
}

function findStaff(staffId) {
    for (var i = 0; i < staffList.length; i++) {
        if (staffList[i].id == staffId) {
            return staffList[i];
        }
    }
    return null;
}

function findProdMaterial(productId) {
    return prodmatList[productId];
}

function findActualMaterial(orderId) {
    return actualmatList[orderId];
}

// Functions for changing currency

async function getFXRate(currency) {

    var url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQAZjOovRAOmI5wAKnCfB7I_8WlpC3BCMGrvVpXkgCYyrjAqwExeX4p5zCCSVTzTUimsYo_MYMs-fWM/pub?output=csv";
    var defaultRates = { JPY: 110, EUR: 0.82, HKD: 7.8 };

    try {

        var res = await fetch(url);
        if (!res.ok) {
            throw new Error("Network response was not ok");
        }
        var text = await res.text();

        var rows = text.trim().split(/\r?\n/).filter(line => line.length).map(line => line.split(",").map(cell => cell.trim()));
        var fxMap = Object.fromEntries(rows);

        if (fxMap[currency]) {
            return parseFloat(fxMap[currency]);
        }
        return defaultRates[currency] ?? -1;

    } catch (e) {

        console.log("FX fetch error:", e);
        return defaultRates[currency] ?? -1;

    }

    // switch(currency) {
    //     case "JPY":
    //         return 110;
    //         break;
    //     case "EUR":
    //         return 0.82;
    //         break;
    //     case "HKD":
    //         return 7.8;
    //         break;
    //     default:
    //         return -1;
    //         break;
    // }

}

function getFXSymbol(currency) {
    switch(currency) {
        case "JPY":
            return "¥";
            break;
        case "EUR":
            return "€";
            break;
        case "HKD":
            return "HK$";
            break;
        default:
            return -1;
            break;
    }
}

async function convertAmount(price, currency) {

    try {

        var rate = await getFXRate(currency);

        result = await $.ajax({
            type: "GET",
            url: `http://localhost:8080/cost_convert/${price}/${currency}/${rate}`,
            dataType: "json"
        });

        if (result.result == "accepted") {
            return ` (${getFXSymbol(currency)}${parseFloat(result.converted_amount).toFixed(2)})`;
        }

        return false;

    } catch (ex) {
        console.log(ex);
    }

}
