// Functions for editing photos

var originalPhoto = document.getElementById("profile-picture").src;
var originalEmail = document.getElementById("email-display").innerText;
var originalTel = document.getElementById("telephone-display").innerText;
var originalAddress = document.getElementById("address-display").innerText;

function handlePhotoUpload(input) {

    if (input.files[0]) {

    var file = input.files[0];

    // Validate file type
    if (!file.type.match("image.*")) {
        alert("Please select a valid image file");
        input.value = "";
        return;
    }

    // Preview the image
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById("profile-picture").src = e.target.result;
        showPhotoActionButtons();
    };
    reader.readAsDataURL(file);

    }

}

function showPhotoActionButtons() {

    document.getElementById("upload-btn").style.display = "none";
    document.getElementById("save-photo-btn").style.display = "inline-block";
    document.getElementById("cancel-photo-btn").style.display = "inline-block";

}

function cancelPhotoUpload() {

    // Reset image to original
    document.getElementById("profile-picture").src = originalPhoto;

    // Reset buttons
    document.getElementById("upload-btn").style.display = "inline-block";
    document.getElementById("save-photo-btn").style.display = "none";
    document.getElementById("cancel-photo-btn").style.display = "none";

}

// Functions for editing password

function editPassword() {

    currentPassword = document.getElementById("current-password");
    newPassword = document.getElementById("new-password");
    confirmPassword = document.getElementById("confirm-password");

    currentPassword.required = true;
    newPassword.required = true;
    confirmPassword.required = true;

    document.getElementById("password-change-form").style.display = "block";
    document.getElementById("password-save-btn").style.display = "inline-block";
    document.getElementById("password-cancel-btn").style.display = "inline-block";
    document.getElementById("current-password").focus();

}

function checkPassword() {

    currentPassword = document.getElementById("current-password");
    newPassword = document.getElementById("new-password");
    confirmPassword = document.getElementById("confirm-password");

    if (newPassword.value !== confirmPassword.value) {
        cancelPassword();
        alert("New passwords do not match!");
        return false;
    }

    if (newPassword.value.length < 6) {
        cancelPassword();
        alert("Password must be at least 6 characters long!");
        return false;
    }

    return true;

}

function cancelPassword() {

    currentPassword = document.getElementById("current-password");
    newPassword = document.getElementById("new-password");
    confirmPassword = document.getElementById("confirm-password");

    currentPassword.required = false;
    newPassword.required = false;
    confirmPassword.required = false;

    document.getElementById("password-change-form").style.display = "none";
    document.getElementById("password-save-btn").style.display = "none";
    document.getElementById("password-cancel-btn").style.display = "none";
    document.getElementById("password-form").reset();

}

// Functions for editing email

function editEmail() {
    
    document.getElementById("email-display").style.display = "none";
    document.getElementById("email-edit-btn").style.display = "none";

    document.getElementById("email-input").style.display = "inline-block";
    document.getElementById("email-input").focus();
    document.getElementById("email-save-btn").style.display = "inline-block";
    document.getElementById("email-cancel-btn").style.display = "inline-block";

}

function saveEmail() {

    var email = document.getElementById("email-input").value;

    if (email.trim() == "") {
        alert("Please enter a valid email!");
        return false;
    }

    return true;

}

function cancelEmail() {

    document.getElementById("email-display").style.display = "inline";
    document.getElementById("email-edit-btn").style.display = "inline-block";

    document.getElementById("email-input").style.display = "none";
    document.getElementById("email-save-btn").style.display = "none";
    document.getElementById("email-cancel-btn").style.display = "none";

    document.getElementById("email-input").value = originalEmail;

}

// Functions for editing telephone

function editTelephone() {

    document.getElementById("telephone-display").style.display = "none";
    document.getElementById("telephone-edit-btn").style.display = "none";

    document.getElementById("telephone-input").style.display = "inline-block";
    document.getElementById("telephone-input").focus();
    document.getElementById("telephone-save-btn").style.display = "inline-block";
    document.getElementById("telephone-cancel-btn").style.display = "inline-block";

}

function saveTelephone() {

    var telephone = document.getElementById("telephone-input").value;
    if (telephone.trim() == "") {
        alert("Please enter a valid contact number!");
        return false;
    }

    return true;

}

function cancelTelephone() {

    document.getElementById("telephone-display").style.display = "inline";
    document.getElementById("telephone-edit-btn").style.display = "inline-block";

    document.getElementById("telephone-input").style.display = "none";
    document.getElementById("telephone-save-btn").style.display = "none";
    document.getElementById("telephone-cancel-btn").style.display = "none";

    document.getElementById("telephone-input").value = originalTel;

}

// Functions for editing address

function editAddress() {

    document.getElementById("address-display").style.display = "none";
    document.getElementById("address-edit-btn").style.display = "none";

    document.getElementById("address-input").style.display = "block";
    document.getElementById("address-input").focus();
    document.getElementById("address-save-btn").style.display = "inline-block";
    document.getElementById("address-cancel-btn").style.display = "inline-block";
    
}

function saveAddress() {

    var address = document.getElementById("address-input").value;
    if (address.trim() == "") {
        alert("Please enter a valid address!");
        return false;
    }

    return true;

}

function cancelAddress() {

    document.getElementById("address-display").style.display = "inline";
    document.getElementById("address-edit-btn").style.display = "inline-block";

    document.getElementById("address-input").style.display = "none";
    document.getElementById("address-save-btn").style.display = "none";
    document.getElementById("address-cancel-btn").style.display = "none";

    document.getElementById("address-input").value = originalAddress;

}
