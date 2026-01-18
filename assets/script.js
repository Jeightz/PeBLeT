function togglepassword(){
    const input = document.getElementById("password");
    if(input.type === "password"){
        input.type = "text"
    }else{
        input.type ="password"
    }
}

document.getElementById("btnlogin").addEventListener("click",async () => {

   const credentials= {
    username:document.getElementById("username").value.trim(),
    password:document.getElementById("password").value.trim()
   }

   if(!credentials.username|| !credentials.password){
        alert("missing please fill up all the creditials");
        return;
   }

 try {
    const response = await fetch("api/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(credentials)
    });

        const result = await response.json();
        console.log(result);
    } catch (error) {
        console.error("Login failed:", error);
        alert("Server error. Try again later.");
    }

});

async function loadLoans() {
    const res = await fetch("api/loan.php");
    const loans = await res.json();
    const tbody = document.querySelector("#loansTable tbody");
    tbody.innerHTML = "";
    loans.forEach(loan => {
        tbody.innerHTML += `
            <tr>
                <td>${loan.loan_name}</td>
                <td>${loan.user_name}</td>
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
}

async function deleteLoan(loan_id){
    await fetch("api/loan.php", {
        method:"DELETE",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({loan_id})
    });
    loadLoans();
}

async function addLoan(){
    const user_id = prompt("User ID:");
    const loan_name = prompt("Loan Name:");
    const amount = prompt("Amount:");
    await fetch("api/loan.php", {
        method:"POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({user_id, loan_name, amount})
    });
    loadLoans();
}

document.getElementById("addLoanBtn").addEventListener("click", addLoan);

loadLoans();
