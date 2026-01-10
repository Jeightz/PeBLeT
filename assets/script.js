function togglepassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}

function signup() {
    document.getElementById("login").style.display = "none";
    document.getElementById("loginfooter").style.display = "none";
    document.getElementById("signupmain").style.display = "flex";
    document.getElementById("signupcard").style.display = "flex";
    document.getElementById("signupfooter").style.display = "block";
}

function login() {
    document.getElementById("signupmain").style.display = "none";
    document.getElementById("signupcard").style.display = "none";
    document.getElementById("signupfooter").style.display = "none";
    document.getElementById("login").style.display = "flex";
    document.getElementById("loginfooter").style.display = "block";
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginemail').value = '';
    document.getElementById('loginpassword').value = '';
    document.getElementById('username').value = '';
    document.getElementById('email').value = '';
    document.getElementById('signpassword').value = '';
    document.getElementById('conpassword').value = '';
    
    const loginForm = document.querySelector('form[action="login.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('loginemail').value.trim();
            const password = document.getElementById('loginpassword').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert("Email and password are required!");
                return false;
            }
        });
    }
    
    const signupForm = document.querySelectorAll('form[action="login.php"]')[1];
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const name = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('signpassword').value.trim();
            const confirm = document.getElementById('conpassword').value.trim();
            
            if (!name || !email || !password || !confirm) {
                e.preventDefault();
                alert("All fields are required!");
                return false;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                alert("Passwords do not match!");
                return false;
            }
        });
    }
});