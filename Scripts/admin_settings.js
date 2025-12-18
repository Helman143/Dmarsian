function showTab(evt, tabId) {
  // Hide all tab contents
  var tabContents = document.getElementsByClassName("tab-content");
  for (var i = 0; i < tabContents.length; i++) {
    tabContents[i].style.display = "none";
  }
  // Remove active class from all tab buttons
  var tabBtns = document.getElementsByClassName("tab-btn");
  for (var i = 0; i < tabBtns.length; i++) {
    tabBtns[i].classList.remove("active");
  }
  // Show the selected tab and set active class
  document.getElementById(tabId).style.display = "block";
  evt.currentTarget.classList.add("active");

  // If switching to the admins-account tab, refresh the table
  if (tabId === "admins-account") {
    refreshAdminAccountsTable();
  }
}

// Function to toggle password visibility
function togglePassword(button) {
  const passwordCell = button.parentElement;
  const maskedSpan = passwordCell.querySelector(".password-masked");
  const actualSpan = passwordCell.querySelector(".password-actual");
  const icon = button.querySelector("i");

  if (maskedSpan.style.display !== "none") {
    maskedSpan.style.display = "none";
    actualSpan.style.display = "inline";
    icon.className = "fas fa-eye-slash";
  } else {
    maskedSpan.style.display = "inline";
    actualSpan.style.display = "none";
    icon.className = "fas fa-eye";
  }
}

// Function to attach event listeners to toggle password buttons
function attachTogglePasswordListeners() {
  const toggleBtns = document.querySelectorAll(".toggle-password-btn");
  toggleBtns.forEach((button) => {
    button.addEventListener("click", function () {
      togglePassword(this);
    });
  });
}

// Function to reset password for an admin account
function resetPassword(userId) {
  if (
    confirm(
      "Are you sure you want to reset the password for this admin account? This will set a default password."
    )
  ) {
    const formData = new FormData();
    formData.append("action", "reset_admin_password");
    formData.append("id", userId);

    fetch("admin_actions.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          alert(
            "Password reset successfully! New password: " + data.new_password
          );
          refreshAdminAccountsTable();
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error resetting password:", error);
        alert("An error occurred while resetting the password.");
      });
  }
}

// Function to attach event listeners to reset password buttons
function attachResetPasswordListeners() {
  const resetBtns = document.querySelectorAll(".reset-password-btn");
  resetBtns.forEach((button) => {
    button.addEventListener("click", function () {
      const userId = this.getAttribute("data-user-id");
      resetPassword(userId);
    });
  });
}

// Function to clear the form fields
function clearAdminForm() {
  document.getElementById("admin-user-id").value = ""; // Clear the hidden ID as well
  document.getElementById("admin-email").value = "";
  document.getElementById("admin-username").value = "";
  document.getElementById("admin-password").value = "";
  document.getElementById("admin-confirm-password").value = "";
}

// Function to populate the form for editing
function populateAdminFormForEdit(userId, email, username, password) {
  document.getElementById("admin-user-id").value = userId;
  document.getElementById("admin-email").value = email;
  document.getElementById("admin-username").value = username;

  // Populate password fields with actual password (now stored in plain text)
  document.getElementById("admin-password").value = password;
  document.getElementById("admin-confirm-password").value = password;
}

// Function to refresh the admin accounts table
function refreshAdminAccountsTable() {
  fetch("get_admin_accounts_with_passwords.php") // Updated to use the new file with passwords
    .then((response) => response.text())
    .then((html) => {
      document.querySelector(".admin-table tbody").innerHTML = html;
      // Re-attach event listeners to new edit buttons
      attachEditButtonListeners();
      attachDeleteButtonListeners();
      attachTogglePasswordListeners();
      attachResetPasswordListeners();
    })
    .catch((error) =>
      console.error("Error refreshing admin accounts table:", error)
    );
}

// Function to attach event listeners to edit buttons
function attachEditButtonListeners() {
  const editBtns = document.querySelectorAll(".action-btn.edit-admin");
  editBtns.forEach((button) => {
    button.addEventListener("click", function () {
      const userId = this.getAttribute("data-id");
      const email = this.getAttribute("data-email");
      const username = this.getAttribute("data-username");
      const password = this.getAttribute("data-password");
      populateAdminFormForEdit(userId, email, username, password);
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  });
}

// Function to attach event listeners to delete buttons
function attachDeleteButtonListeners() {
  const deleteBtns = document.querySelectorAll(".action-btn.delete-admin");
  deleteBtns.forEach((button) => {
    button.addEventListener("click", function () {
      const userId = this.getAttribute("data-id");
      if (confirm("Are you sure you want to delete this admin account?")) {
        const formData = new FormData();
        formData.append("action", "delete_admin");
        formData.append("id", userId);

        fetch("admin_actions.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status === "success") {
              alert(data.message);
              refreshAdminAccountsTable();
            } else {
              alert("Error: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error deleting admin account:", error);
            alert("An error occurred while deleting the account.");
          });
      }
    });
  });
}

// Password strength validator for Admins Account form
function isStrongPassword(pw) {
  return (
    typeof pw === 'string' &&
    pw.length >= 8 &&
    /[a-z]/.test(pw) &&
    /[A-Z]/.test(pw) &&
    /\d/.test(pw) &&
    /[^A-Za-z0-9]/.test(pw) &&
    !/\s/.test(pw)
  );
}

// Function to handle form submission
document.addEventListener("DOMContentLoaded", function () {
  const saveBtn = document.querySelector(".action-btn.save");
  const updateBtn = document.querySelector(".action-btn.update");
  const clearBtn = document.querySelector(".action-btn.clear");
  const exportBtn = document.querySelector(".action-btn.export");

  // Save button click handler
  saveBtn.addEventListener("click", function () {
    const form = document.querySelector(".admin-account-form");
    const pw = document.getElementById("admin-password").value;
    const cpw = document.getElementById("admin-confirm-password").value;
    if (!isStrongPassword(pw)) {
      alert("Password must be 8+ chars with upper, lower, number, special.");
      return;
    }
    if (pw !== cpw) {
      alert("Passwords do not match.");
      return;
    }
    const formData = new FormData();
    formData.append("action", "save_admin");
    formData.append("email", document.getElementById("admin-email").value);
    formData.append(
      "username",
      document.getElementById("admin-username").value
    );
    formData.append(
      "password",
      document.getElementById("admin-password").value
    );
    formData.append(
      "confirm_password",
      document.getElementById("admin-confirm-password").value
    );

    fetch("admin_actions.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          alert(data.message);
          clearAdminForm();
          refreshAdminAccountsTable();
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error saving admin account:", error);
        alert("An error occurred while saving the account.");
      });
  });

  // Update button click handler
  updateBtn.addEventListener("click", function () {
    const userId = document.getElementById("admin-user-id").value;
    if (!userId) {
      alert("Please select an admin account to update first.");
      return;
    }
    const upw = document.getElementById("admin-password").value;
    const ucpw = document.getElementById("admin-confirm-password").value;
    if (upw) {
      if (!isStrongPassword(upw)) {
        alert("Password must be 8+ chars with upper, lower, number, special.");
        return;
      }
      if (upw !== ucpw) {
        alert("Passwords do not match.");
        return;
      }
    }

    const formData = new FormData();
    formData.append("action", "super_admin_update"); // Use super admin update action
    formData.append("id", userId);
    formData.append("email", document.getElementById("admin-email").value);
    formData.append(
      "username",
      document.getElementById("admin-username").value
    );
    formData.append(
      "password",
      document.getElementById("admin-password").value
    );
    formData.append(
      "confirm_password",
      document.getElementById("admin-confirm-password").value
    );

    fetch("admin_actions.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          alert(data.message);
          clearAdminForm();
          refreshAdminAccountsTable();
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error updating admin account:", error);
        alert("An error occurred while updating the account.");
      });
  });

  // Clear button click handler
  clearBtn.addEventListener("click", clearAdminForm);

  // Export button click handler (if button exists)
  if (exportBtn) {
    exportBtn.addEventListener("click", function () {
      window.location.href = "export_admin_accounts.php";
    });
  }

  // Initial load of admin accounts
  refreshAdminAccountsTable();
});
