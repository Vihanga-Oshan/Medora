function nextStep() {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirmPassword").value;

    if (password !== confirm) {
        alert("Passwords do not match.");
        return;
    }

    document.getElementById("step1").style.display = "none";
    document.getElementById("step2").style.display = "block";
}

function previousStep() {
    document.getElementById("step2").style.display = "none";
    document.getElementById("step1").style.display = "block";
}
