<?php
session_start();

$signupSuccess = "";
$signupError = "";
$loginSuccess = "";
$loginError = "";
$userData = null;



if (isset($_POST['signup'])) {
    $name = trim($_POST['username'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirmpassword = $_POST['confirmpassword'] ?? "";

    if ($name === "" || $email === "" || $password === "" || $confirmpassword === "") {
        $signupError = "All fields are required for signup";
    } elseif ($password !== $confirmpassword) {
        $signupError = "Passwords do not match";
    } else {
        $hashedPassword = hash("sha256", $password);

        unset($_SESSION['signup_name'], $_SESSION['signup_email'], $_SESSION['signup_org_password'], $_SESSION['signup_password']);
        
        // Set new session data
        $_SESSION['signup_name'] = $name;
        $_SESSION['signup_email'] = $email;
        $_SESSION['signup_org_password'] = $password;
        $_SESSION['signup_password'] = $hashedPassword;

        $signupSuccess = "Signup successful! You can now login.";
    }
}

if (isset($_POST['login'])) {
    $loginEmail = trim($_POST['loginemail'] ?? "");
    $loginPassword = $_POST['loginpassword'] ?? "";

    if ($loginEmail === "" || $loginPassword === "") {
        $loginError = "Email and password are required for login";
    } elseif (!isset($_SESSION['signup_email']) || empty($_SESSION['signup_email'])) {
        $loginError = "No account found. Please sign up first.";
    } else {
        $loginHashed = hash("sha256", $loginPassword);

        if ($loginEmail === $_SESSION['signup_email'] && $loginHashed === $_SESSION['signup_password']) {
            $loginSuccess = "Login successful! Welcome " . $_SESSION['signup_name'];
            $userData = [
                'name' => $_SESSION['signup_name'],
                'email' => $_SESSION['signup_email'],
                'password' => $_SESSION['signup_org_password'],
                'hashedPassword' => $_SESSION['signup_password']
            ];
        } else {
            $loginError = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PebLet</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <form action="login.php" method="post">
        <main id="login">
            <div id="logincard" class="logincard">
                <div class="logos">
                    <img src="img/logo.png" class="logo" alt="PebLet">
                    <p>PebLet</p>
                </div>
                
                <div class="input-box">
                    <img src="img/user.png" class="left-icon">
                    <input type="text" id="loginemail" name="loginemail" placeholder="Enter email" value="">
                </div>
                
                <div class="input-box">
                    <img src="img/lock.png" class="left-icon">
                    <input type="password" id="loginpassword" name="loginpassword" placeholder="Enter password" value="">
                    <span class="right-icon" onclick="togglepassword('loginpassword')">👁</span>
                </div>
                
                <button type="submit" class="btns" name="login">Login</button>
                <hr>
                
                <button type="button" class="loginbtn">
                    <img class="loginicon" src="img/google.png" alt="Google">
                    <span class="logotext">Continue With Google</span>
                </button>
                
                <button type="button" class="loginbtn">
                    <img class="loginicon" src="img/facebook.png" alt="Facebook">
                    <span class="logotext">Continue With Facebook</span>
                </button>
                
                <button type="button" class="loginbtn">
                    <img id="microsoft" class="loginicon" src="img/microsoft.png" alt="Microsoft">
                    <span class="logotext">Continue With Microsoft</span>
                </button>
            </div>
        </main>
    </form>

    <main id="signupmain">
        <form action="login.php" method="post">
            <div id="signupcard">
                <p style="text-align:center">Signup</p>
                
                <div class="input-box">
                    <img src="img/user.png" class="left-icon">
                    <input type="text" id="username" name="username" placeholder="Enter Name" value="">
                </div>
                
                <div class="input-box">
                    <img src="img/user.png" class="left-icon">
                    <input type="text" id="email" name="email" placeholder="Enter email" value="">
                </div>
                
                <div class="input-box">
                    <img src="img/lock.png" class="left-icon">
                    <input type="password" id="signpassword" name="password" placeholder="Enter password" value="">
                    <span class="right-icon" onclick="togglepassword('signpassword')">👁</span>
                </div>
                
                <div class="input-box">
                    <img src="img/lock.png" class="left-icon">
                    <input type="password" id="conpassword" name="confirmpassword" placeholder="Confirm password" value="">
                    <span class="right-icon" onclick="togglepassword('conpassword')">👁</span>
                </div>
                
                <button type="submit" class="btns" name="signup">Signup</button>
                <hr>
                
                <button type="button" class="loginbtn">
                    <img class="loginicon" src="img/google.png" alt="Google">
                    <span class="logotext">Signup With Google</span>
                </button>
                
                <button type="button" class="loginbtn">
                    <img class="loginicon" src="img/facebook.png" alt="Facebook">
                    <span class="logotext">Signup With Facebook</span>
                </button>
                
                <button type="button" class="loginbtn">
                    <img id="microsoft" class="loginicon" src="img/microsoft.png" alt="Microsoft">
                    <span class="logotext">Signup With Microsoft</span>
                </button>
            </div>
        </form>
    </main>

    <footer id="loginfooter">
        Don't Have Account? <button type="button" class="nav" onclick="signup()">SignUp</button>
        <br>
    </footer>
    
    <footer id="signupfooter">
        Already Have Account? <button type="button" class="nav" onclick="login()">Login</button>
    </footer>

    <script>
        
        const signupSuccess = "<?php echo addslashes($signupSuccess); ?>";
        const signupError = "<?php echo addslashes($signupError); ?>";
        const loginSuccess = "<?php echo addslashes($loginSuccess); ?>";
        const loginError = "<?php echo addslashes($loginError); ?>";
        const USER_DATA = <?php echo json_encode($userData); ?>;
        
        if(signupSuccess && signupSuccess.length > 0) {
            alert("SIGNUP SUCCESS:\n" + signupSuccess);
        }
        if(signupError && signupError.length > 0) {
            alert("SIGNUP ERROR:\n" + signupError);
        }
        if(loginSuccess && loginSuccess.length > 0) {
            if(USER_DATA) {
                alert("LOGIN SUCCESS:\n" + loginSuccess + 
                      "\n\nUser Details:" + 
                      "\nName: " + USER_DATA.name + 
                      "\nEmail: " + USER_DATA.email+
                        "\nhashedPassword: " + USER_DATA.hashedPassword);
            } else {
                alert("LOGIN SUCCESS:\n" + loginSuccess);
            }
        }
        if(loginError && loginError.length > 0) {
            alert("LOGIN ERROR:\n" + loginError);
        }
    </script>

    <script src="assets/script.js"></script>
</body>
</html>