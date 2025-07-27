function displayUsers() {

    var tbody = document.getElementById("users-body");
    var filteredUsers = filterUsersBySearch(customerList);
    
    var html = "";
    
    for (var i = 0; i < filteredUsers.length; i++) {
        var user = filteredUsers[i];
        
        html += `
            <tr class="item-row">
                <td class="col1">${user.id}</td>
                <td class="col2"><div class="item-name">${user.name}</div></td>
                <td class="col3"><a href="mailto:${user.email}">${user.email}</a></td>
                <td class="col4"><a href="tel:${user.telephone}">${user.telephone}</a></td>
                <td class="col5">${user.address}</td>
                <td class="col6">${user.company}</td>
                <td class="col7">
                    <button class="action-btn view-btn" onclick="showCustomerModal(${user.id})">View</button>
                    <button class="action-btn edit-btn" onclick="window.location.href='staff/user/edit-customer.php?id=${user.id}'">Edit</button>
                    <button class="action-btn delete-btn" onclick="confirmDelete(${user.id})">Delete</button>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
    document.getElementById("item-count").innerHTML = filteredUsers.length;

}

function filterUsersBySearch(users) {

    var searchTerm = document.getElementById("search").value.toLowerCase();
    
    if (searchTerm === "") {
        return users;
    }
    
    var result = [];
    for (var i = 0; i < users.length; i++) {
        if (users[i].name.toLowerCase().indexOf(searchTerm) !== -1 ||
            users[i].email.toLowerCase().indexOf(searchTerm) !== -1 ||
            users[i].company.toLowerCase().indexOf(searchTerm) !== -1) {
            result.push(users[i]);
        }
    }
    
    return result;
}

function performSearch() {
    displayUsers();
}

function resetSearch() {
    document.getElementById("search").value = "";
    displayUsers();
}

function showCustomerModal(userId) {

    var user = findCustomer(userId);

    if (!user) {
        alert("Customer not found!");
        return;
    }

    var imageSource = (user.image && user.image.trim() != "") ? `customer/photos/${user.image}` : `img/chatbox/user.svg`;

    var modalContent = document.getElementById("modal-inner");

    modalContent.innerHTML = `
        <div id="item-details">
        
            <div id="item-image-section">
                <img src="${imageSource}" id="modal-image">
            </div>
            
            <div id="item-info-section">
                <table id="item-info-table">
                    <tr>
                        <td><strong>Customer ID:</strong></td>
                        <td>${user.id}</td>
                    </tr>
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>${user.name}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><a href="mailto:${user.email}">${user.email}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Telephone:</strong></td>
                        <td><a href="tel:${user.telephone}">${user.telephone}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>${user.address}</td>
                    </tr>
                    <tr>
                        <td><strong>Company:</strong></td>
                        <td>${user.company}</td>
                    </tr>
                </table>
            </div>
            
            <div id="action-buttons-modal">
                <button type="button" id="btn-edit" onclick="window.location.href='staff/user/edit-customer.php?id=${user.id}'">Edit Customer</button>
                <button type="button" id="btn-delete" onclick="confirmDelete(${user.id})">Delete Customer</button>
            </div>
            
        </div>
    `;

    document.getElementById("itemModal").style.display = "block";

}

function closeCustomerModal() {
    document.getElementById("itemModal").style.display = "none";
}

function confirmDelete(userId) {
    if (confirm("Are you sure you want to delete this user? This action cannot be undone.")) {
        window.location.href = 'staff/user/delete-customer.php?id=' + encodeURIComponent(userId);
    }
}

// Initial display of customers

displayUsers();