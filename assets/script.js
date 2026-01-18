function togglepassword() {
    const input = document.getElementById("password");
    if (input) {
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
}

// LOGIN FUNCTIONALITY
const loginBtn = document.getElementById("btnlogin");
if (loginBtn) {
    loginBtn.addEventListener("click", async (e) => {
        e.preventDefault();

        const credentials = {
            username: document.getElementById("username").value.trim(),
            password: document.getElementById("password").value.trim()
        };

        if (!credentials.username || !credentials.password) {
            alert("Missing! Please fill up all the credentials");
            return;
        }

        console.log("Attempting login...");

        try {
            const response = await fetch("./api/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(credentials)
            });

            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                const text = await response.text();
                console.error("Server returned non-JSON response:", text);
                alert("Server error: Expected JSON but got HTML. Check PHP file.");
                return;
            }

            const result = await response.json();
            console.log("Login result:", result);

            if (result.success) {
                alert("Login successful!");
                window.location.href = "loans.html";
            } else {
                alert(result.message || "Invalid credentials");
            }

        } catch (error) {
            console.error("Login failed:", error);
            alert("Server error: " + error.message);
        }
    });
}

// SIGNUP FUNCTIONALITY
const signupBtn = document.getElementById("btnSignupTisoy");
if (signupBtn) {
    signupBtn.addEventListener("click", async (e) => {
        e.preventDefault();

        const signupData = {
            username: document.getElementById("username").value.trim(),
            email: document.getElementById("email").value.trim(),
            password: document.getElementById("password").value.trim(),
            confirmpassword: document.getElementById("confirmpassword").value.trim()
        };

        if (!signupData.username || !signupData.email || !signupData.password || !signupData.confirmpassword) {
            alert("All fields are required!");
            return;
        }

        if (signupData.password !== signupData.confirmpassword) {
            alert("Passwords do not match!");
            return;
        }

        console.log("Sending signup data:", signupData);

        try {
            const response = await fetch("./api/signup.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(signupData)
            });

            console.log("Response status:", response.status);

            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                const text = await response.text();
                console.error("Server returned non-JSON response:", text);
                alert("Server error: PHP file has errors. Check browser console for details.");
                return;
            }

            const result = await response.json();
            console.log("Signup result:", result);

            if (result.success) {
                alert("Signup successful! Redirecting to login...");
                window.location.href = "login.html";
            } else {
                alert(result.message || "Signup failed");
            }

        } catch (error) {
            console.error("Signup failed:", error);
            alert("Server error: " + error.message + ". Check browser console for details.");
        }
    });
}

// LOANS FUNCTIONALITY
async function loadLoans() {
    try {
        const res = await fetch("./api/loan.php");
        
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const text = await res.text();
            console.error("Server returned non-JSON response:", text);
            return;
        }
        
        const loans = await res.json();
        console.log("Loans data:", loans);
        
        const tbody = document.querySelector("#loansTable tbody");
        
        if (!tbody) return;
        
        tbody.innerHTML = "";
        
        // Check if loans is an error object
        if (loans.success === false) {
            console.error("Error loading loans:", loans.error);
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">Error: ${loans.error}</td></tr>`;
            return;
        }
        
        // Check if loans is actually an array
        if (!Array.isArray(loans)) {
            console.error("Loans is not an array:", loans);
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">Unexpected data format</td></tr>`;
            return;
        }
        
        // If empty array
        if (loans.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No loans found</td></tr>`;
            return;
        }
        
        loans.forEach(loan => {
            tbody.innerHTML += `
                <tr>
                    <td>${loan.loan_name}</td>
                    <td>${loan.user_name || 'N/A'}</td>
                    <td>${loan.amount}</td>
                    <td>${loan.status}</td>
                    <td>${loan.created_at}</td>
                    <td>
                        <button onclick='editLoan("${loan.loan_id}")'>Edit</button>
                        <button onclick='deleteLoan("${loan.loan_id}")'>Delete</button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Failed to load loans:", error);
    }
}

async function deleteLoan(loan_id) {
    if (!confirm("Are you sure you want to delete this loan?")) return;
    
    try {
        await fetch("./api/loan.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ loan_id })
        });
        loadLoans();
    } catch (error) {
        console.error("Failed to delete loan:", error);
        alert("Failed to delete loan");
    }
}

async function addLoan() {
    const user_id = prompt("User ID:");
    const loan_name = prompt("Loan Name:");
    const amount = prompt("Amount:");
    
    if (!user_id || !loan_name || !amount) {
        alert("All fields are required!");
        return;
    }
    
    try {
        await fetch("./api/loan.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ user_id, loan_name, amount })
        });
        loadLoans();
    } catch (error) {
        console.error("Failed to add loan:", error);
        alert("Failed to add loan");
    }
}

async function editLoan(loan_id) {
    const loan_name = prompt("New Loan Name:");
    const amount = prompt("New Amount:");
    const status = prompt("New Status (pending/approved/rejected):");
    
    if (!loan_name || !amount || !status) {
        alert("All fields are required!");
        return;
    }
    
    try {
        await fetch("./api/loan.php", {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ loan_id, loan_name, amount, status })
        });
        loadLoans();
    } catch (error) {
        console.error("Failed to edit loan:", error);
        alert("Failed to edit loan");
    }
}

const addLoanBtn = document.getElementById("addLoanBtn");
if (addLoanBtn) {
    addLoanBtn.addEventListener("click", addLoan);
    loadLoans();
}