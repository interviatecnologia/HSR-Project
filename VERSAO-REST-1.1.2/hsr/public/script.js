let isRegistered = false;

document.getElementById('sip-credentials-form').addEventListener('submit', function(event) {
  event.preventDefault();
  toggleStatus();
});

document.getElementById('unregister-button').addEventListener('click', function() {
  isRegistered = false;
  updateButtonStatus();
});

function toggleStatus() {
  isRegistered = !isRegistered;
  updateButtonStatus();
}

function updateButtonStatus() {
  const button = document.getElementById("start-button");
  
  if (isRegistered) {
    button.className = "button registered";
    button.textContent = "Registered";
    document.getElementById("status").style.display = "block";
    document.getElementById("status-unregistered").style.display = "none";
  } else {
    button.className = "button not-registered";
    button.textContent = "Register SIP";
    document.getElementById("status").style.display = "none";
    document.getElementById("status-unregistered").style.display = "block";
  }
}

