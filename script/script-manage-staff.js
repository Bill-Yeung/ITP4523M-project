// Functions for displaying staff

function displayStaff(staff) {

    var staffTable = document.getElementById("staff-body");

    staffTable.innerHTML = ``;
    
    for (var i = 0; i < staff.length; i++) {

        var member = staff[i];

        staffTable.innerHTML += `
            <tr class="item-row">
                <td class="col1">${member.id}</td>
                <td class="col2 item-name">${member.name}</td>
                <td class="col3">${member.email}</td>
                <td class="col4">${member.role}</td>
                <td class="col5">${member.phone}</td>
                <td class="col6">
                    <button type="button" class="action-btn view-btn" onclick="showStaffModal(${member.id})">View</button>
                    <button type="button" class="action-btn edit-btn" onclick="window.location.href='staff/user/edit-staff.php?id=${member.id}'">Edit</button>
                    <button type="button" class="action-btn delete-btn" onclick="deleteStaff(${member.id})">Delete</button>
                </td>
            </tr>
        `;
    }

}

// Initial display of the staff

displayStaff(staffList);

// Functions for searching and sorting staff

var currentList = staffList;

function performSearch() {
    
    var searchString = document.getElementById("search");
    var searchWord = searchString.value.toLowerCase();
    var filteredStaff = [];
    
    if (searchWord == "" || searchWord == null) {

        filteredStaff = staffList;

    } else {

        for (var i = 0; i < staffList.length; i++) {

            var member = staffList[i];
            if (member.name.toLowerCase().includes(searchWord) || 
                member.role.toLowerCase().includes(searchWord) ||
                member.email.toLowerCase().includes(searchWord)) {
                filteredStaff[filteredStaff.length] = member;
            }

        }

    }

    currentList = filteredStaff;
    displayStaff(filteredStaff);

    var staffCount = document.getElementById("item-count");
    staffCount.innerHTML = filteredStaff.length;

}

function resetSearch() {

    document.getElementById("search").value = "";
    currentList = staffList;
    displayStaff(staffList);

    var staffCount = document.getElementById("item-count");
    staffCount.innerHTML = staffList.length;

}

// Functions for displaying view

function showStaffModal(staffId) {

    var member = findStaff(staffId);

    if (!member) {
        alert("Staff member not found!");
        return;
    }

    var imageSource = (member.image && member.image.trim() != "") ? `staff/photos/${member.image}` : `img/chatbox/user.svg`;

    var modalContent = document.getElementById("modal-inner");

        modalContent.innerHTML = `

            <div id="item-details">
            
                <div id="item-image-section">
                    <img src="${imageSource}" id="modal-image">
                </div>
                
                <div id="item-info-section">
                    <table id="item-info-table">
                        <tr>
                            <td><strong>Staff ID:</strong></td>
                            <td>${member.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>${member.name}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>${member.email}</td>
                        </tr>
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td>${member.role}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>${member.phone}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>${member.availability == 1 ? "Active" : "Inactive"}</td>
                        </tr>
                    </table>
                </div>
                
                <div id="action-buttons-modal">
                    <button type="button" id="btn-edit" onclick="window.location.href='staff/user/edit-staff.php?id=${member.id}'">Edit Staff</button>
                    <button type="button" id="btn-delete" onclick="deleteStaff(${member.id})">Delete Staff</button>
                </div>
                
        </div>

        `;

    document.getElementById("itemModal").style.display = "block";

}

function closeStaffModal() {
    document.getElementById("itemModal").style.display = "none";
}

function deleteStaff(staffId) {
    if (confirm(`Are you sure you want to delete "${findStaff(staffId).name}"? This action cannot be undone.`)) {
        window.location.href = `staff/user/delete-staff.php?id=${staffId}`;
    }
}

