// Functions for displaying materials

function displayMaterials(materials) {

    var materialTable = document.getElementById("materials-body");

    materialTable.innerHTML = ``;
    
    for (var i = 0; i < materials.length; i++) {

        var material = materials[i];
        var remainingQty = material.quantity - material.reserved;
        var isLowStock = remainingQty <= material.reorder;

        materialTable.innerHTML += `
            <tr class="item-row">
                <td class="material-col1">${material.id}</td>
                <td class="material-col2 item-name">${material.name}</td>
                <td class="material-col3"><img src="img/material/${material.image}" class="item-image"></td>
                <td class="material-col4">${material.unit}</td>
                <td class="material-col5">${material.quantity}</td>
                <td class="material-col6">${material.reserved}</td>
                <td class="material-col7 ${isLowStock ? 'low-stock' : 'adequate-stock'}">${remainingQty}</td>
                <td class="material-col8">${material.reorder}</td>
                <td class="material-col9">${material.availability == 1 ? "Available" : "Deactivated"}</td>
                <td class="material-col10">
                    <button type="button" class="action-btn view-btn" onclick="showMaterialModal(${material.id})">View</button>
                    ${material.availability == 1 ? `<button type="button" class="action-btn edit-btn" onclick="window.location.href='staff/material/edit-material.php?mid=${material.id}'">Edit</button>` : `` }
                    ${material.availability == 1 ? `<button type="button" class="action-btn delete-btn" onclick="deleteMaterial(${material.id})">Delete</button>` : `` }
                </td>
            </tr>
        `;
    }

}

function applyMaterialFilter() {

    var filter = document.getElementById("status-filter").value;
    var searchTerm = document.getElementById("search").value.toLowerCase();
    
    var filteredMaterials = materialList.filter(function(material) {
        var matchesSearch = material.name.toLowerCase().includes(searchTerm);
        var matchesFilter = true;
        
        if (filter === "active") {
            matchesFilter = material.availability == 1;
        } else if (filter === "inactive") {
            matchesFilter = material.availability == 0;
        }
        
        return matchesSearch && matchesFilter;
    });
    
    displayMaterials(filteredMaterials);
    updateMaterialCount(filteredMaterials.length);

}

function updateMaterialCount(count) {
    document.getElementById("item-count").innerHTML = count;
}

// Initial display of the materials

applyMaterialFilter();

// Functions for searching and sorting materials

var currentList = materialList;

function performSearch() {
    
    applyMaterialFilter();

}

function resetSearch() {

    document.getElementById("search").value = "";
    document.getElementById("status-filter").value = "active";
    applyMaterialFilter();

}

// Functions for displaying view

function showMaterialModal(materialId) {

    var material = findMaterial(materialId);

    if (!material) {
        alert("Material not found!");
        return;
    }

    var modalContent = document.getElementById("modal-inner");

        modalContent.innerHTML = `

            <div id="item-details">
            
                <div id="item-image-section">
                    <img src="img/material/${material.image}" id="modal-image">
                </div>
                
                <div id="item-info-section">
                    <table id="item-info-table">
                        <tr>
                            <td><strong>Material ID:</strong></td>
                            <td>${material.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Material Name:</strong></td>
                            <td>${material.name}</td>
                        </tr>
                        <tr>
                            <td><strong>Physical Quantity:</strong></td>
                            <td>${material.quantity} ${material.unit}</td>
                        </tr>
                        <tr>
                            <td><strong>Reserved Quantity:</strong></td>
                            <td>${material.quantity} ${material.unit}</td>
                        </tr>
                        <tr>
                            <td><strong>Remaining Quantity:</strong></td>
                            <td>${material.quantity - material.reserved} ${material.unit}</td>
                        </tr>
                        <tr>
                            <td><strong>Re-order Level:</strong></td>
                            <td>${material.reorder} ${material.unit}</td>
                        </tr>
                        <tr>
                            <td><strong>Availability:</strong></td>
                            <td>${material.availability == 1 ? "Available" : "Deactivated"}</td>
                        </tr>
                    </table>
                </div>
                
                ${material.availability == 1 ? `
                <div id="action-buttons-modal">
                    <button type="button" id="btn-edit" onclick="window.location.href='staff/material/edit-material.php?mid=${material.id}'">Edit Material</button>
                    <button type="button" id="btn-delete" onclick="deleteMaterial(${material.id})">Delete Material</button>
                </div>
                ` : `
                <div id="action-buttons-modal">
                    <p id="terminated-message">This material is deactivated and cannot be modified.</p>
                </div>
                `}
                
        </div>

        `;

    document.getElementById("itemModal").style.display = "block";

}

function closeMaterialModal() {
    document.getElementById("itemModal").style.display = "none";
}

function deleteMaterial(materialId) {
    if (confirm(`Are you sure you want to delete "${findMaterial(materialId).name}"? This action cannot be undone.`)) {
        window.location.href = `staff/material/delete-material.php?mid=${materialId}`;
    }
}

