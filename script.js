function getUsers() {
    return JSON.parse(localStorage.getItem("users")) || [];
  }
  
  // login page 
  function login() {
    const email = document.getElementById("login-email").value.trim();
    const password = document.getElementById("login-password").value;
  
    const users = getUsers();
    const user = users.find(u => u.email === email && u.password === password);
  
    if (!user) {
      document.getElementById("login-error").textContent = "Invalid credentials.";
      return;
    }
  
    localStorage.setItem("loggedInUser", JSON.stringify(user));
    window.location.href = "dashboard.html";
  }
  
// Signup page 
  function signup() {
    const name = document.getElementById("signup-name").value.trim();
    const email = document.getElementById("signup-email").value.trim();
    const address = document.getElementById("signup-address").value.trim();
    const password = document.getElementById("signup-password").value;
  
    const errorEl = document.getElementById("signup-error");
  
    // Validations
    if (name.length < 20 || name.length > 60) {
      errorEl.textContent = "Name must be between 20 and 60 characters.";
      return;
    }
  
    if (address.length > 400) {
      errorEl.textContent = "Address must be under 400 characters.";
      return;
    }
  
    const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,16}$/;
    if (!passwordRegex.test(password)) {
      errorEl.textContent = "Password must be 8-16 chars with 1 uppercase & 1 special character.";
      return;
    }
  
    let users = getUsers();
    if (users.some(u => u.email === email)) {
      errorEl.textContent = "Email already exists.";
      return;
    }
  
    users.push({ name, email, address, password, role: "user" });
    localStorage.setItem("users", JSON.stringify(users));
    alert("Registration successful. You can now log in.");
    window.location.href = "index.html";
  }

  

  function logout() {
    localStorage.removeItem("loggedInUser");
    window.location.href = "index.html";
  }


   // Dashboard
   
  function loadDashboard() 
  
  {


    
    const user = JSON.parse(localStorage.getItem("loggedInUser"));
    const container = document.getElementById("dashboard-content");
    const title = document.getElementById("dashboard-title");
  
    
    if (!user) {
      alert("Please login first.");
      window.location.href = "index.html";
      return;
    }
  
    if (user.role === "admin") {
      title.textContent = "System Administrator Dashboard";
      const users = getUsers();
      const stores = JSON.parse(localStorage.getItem("stores")) || [];
      const ratings = JSON.parse(localStorage.getItem("ratings")) || [];
  
      container.innerHTML = `
        <p>Total Users: ${users.length}</p>
        <p>Total Stores: ${stores.length}</p>
        <p>Total Ratings: ${ratings.length}</p>
        <hr>
        <h3>Add User or Store</h3>
        <button onclick="showUserForm()">Add User</button>
        <button onclick="showStoreForm()">Add Store</button>
        <div id="admin-forms"></div>
      `;
    } else if (user.role === "user") {
      title.textContent = "User Dashboard";

      const passwordBtn = document.createElement("button");
  passwordBtn.textContent = "Change Password";
  passwordBtn.onclick = showPasswordForm;
  title.after(passwordBtn);

      loadStoreList(user);
    } 
    else if (user.role === "owner") {
      title.textContent = "Store Owner Dashboard";
      loadOwnerView(user);
    }


    
  }


  //showUserForm

  function showUserForm() {
    const form = `
      <h4>Add New User</h4>
      <input id="admin-name" placeholder="Name">
      <input id="admin-email" placeholder="Email">
      <input id="admin-password" placeholder="Password">
      <input id="admin-address" placeholder="Address">
      <select id="admin-role">
        <option value="user">Normal User</option>
        <option value="admin">Admin</option>
        <option value="owner">Store Owner</option>
      </select>
      <button onclick="addUserByAdmin()">Add User</button>
    `;
    document.getElementById("admin-forms").innerHTML = form;
  }
  
  function addUserByAdmin() {
    const name = document.getElementById("admin-name").value;
    const email = document.getElementById("admin-email").value;
    const password = document.getElementById("admin-password").value;
    const address = document.getElementById("admin-address").value;
    const role = document.getElementById("admin-role").value;
  
    const users = getUsers();
    if (users.some(u => u.email === email)) {
      alert("Email already exists.");
      return;
    }
  
    users.push({ name, email, password, address, role });
    localStorage.setItem("users", JSON.stringify(users));
    alert("User added successfully!");
  }
  
  function showStoreForm() {
    const form = `
      <h4>Add New Store</h4>
      <input id="store-name" placeholder="Store Name">
      <input id="store-email" placeholder="Email">
      <input id="store-address" placeholder="Address">
      <button onclick="addStore()">Add Store</button>
    `;
    document.getElementById("admin-forms").innerHTML = form;
  }
  
  function addStore() {
    const name = document.getElementById("store-name").value;
    const email = document.getElementById("store-email").value;
    const address = document.getElementById("store-address").value;
  
    const stores = JSON.parse(localStorage.getItem("stores")) || [];
    stores.push({ id: Date.now(), name, email, address });
    localStorage.setItem("stores", JSON.stringify(stores));
    alert("Store added successfully!");
  }



  function loadStoreList(currentUser) {
    const stores = JSON.parse(localStorage.getItem("stores")) || [];
    const ratings = JSON.parse(localStorage.getItem("ratings")) || [];
    const container = document.getElementById("dashboard-content");
  
    let html = `
      <input type="text" id="search" placeholder="Search by Name or Address" oninput="searchStores(this.value)">
      <div id="store-list">
        ${renderStoreList(stores, ratings, currentUser)}
      </div>
    `;
  
    container.innerHTML = html;
  }
  
  function renderStoreList(stores, ratings, user) {
    return stores.map(store => {
      const userRatingObj = ratings.find(r => r.user === user.email && r.storeId === store.id);
      const storeRatings = ratings.filter(r => r.storeId === store.id);
      const avgRating = storeRatings.length
        ? (storeRatings.reduce((a, b) => a + b.rating, 0) / storeRatings.length).toFixed(1)
        : "No ratings";
  
      return `
        <div class="store-card">
          <h4>${store.name}</h4>
          <p>Address: ${store.address}</p>
          <p>Average Rating: ${avgRating}</p>
          <p>Your Rating: ${userRatingObj ? userRatingObj.rating : "Not Rated"}</p>
          <select onchange="submitRating(${store.id}, this.value)">
            <option value="">Rate this store</option>
            ${[1,2,3,4,5].map(n => `<option value="${n}" ${userRatingObj?.rating === n ? "selected" : ""}>${n}</option>`).join('')}
          </select>
        </div>
      `;
    }).join("");
  }


  function submitRating(storeId, ratingValue) {
    const user = JSON.parse(localStorage.getItem("loggedInUser"));
    if (!user) return;
  
    let ratings = JSON.parse(localStorage.getItem("ratings")) || [];
  
    const existing = ratings.find(r => r.user === user.email && r.storeId === storeId);
    if (existing) {
      existing.rating = parseInt(ratingValue);
    } else {
      ratings.push({ user: user.email, storeId, rating: parseInt(ratingValue) });
    }
  
    localStorage.setItem("ratings", JSON.stringify(ratings));
    alert("Rating submitted!");
    loadDashboard(); // reload dashboard to reflect changes
  }
  


  function searchStores(query) {
    const user = JSON.parse(localStorage.getItem("loggedInUser"));
    const allStores = JSON.parse(localStorage.getItem("stores")) || [];
    const ratings = JSON.parse(localStorage.getItem("ratings")) || [];
  
    const filtered = allStores.filter(s =>
      s.name.toLowerCase().includes(query.toLowerCase()) ||
      s.address.toLowerCase().includes(query.toLowerCase())
    );
  
    const storeListEl = document.getElementById("store-list");
    storeListEl.innerHTML = renderStoreList(filtered, ratings, user);
  }


  function loadOwnerView(owner) {
    const stores = JSON.parse(localStorage.getItem("stores")) || [];
    const ratings = JSON.parse(localStorage.getItem("ratings")) || [];
    const users = getUsers();
  
    const myStore = stores.find(s => s.email === owner.email);
    if (!myStore) {
      document.getElementById("dashboard-content").innerHTML = `
        <p>You don't have a store registered yet.</p>
      `;
      return;
    }
  
    const myRatings = ratings.filter(r => r.storeId === myStore.id);
    const avgRating = myRatings.length
      ? (myRatings.reduce((a, b) => a + b.rating, 0) / myRatings.length).toFixed(1)
      : "No ratings yet";
  
    let html = `
      <h3>My Store: ${myStore.name}</h3>
      <p>Address: ${myStore.address}</p>
      <p>Average Rating: ${avgRating}</p>
      <hr>
      <h4>Users who rated your store:</h4>
      <table>
        <thead>
          <tr>
            <th>User Name</th>
            <th>Email</th>
            <th>Rating</th>
          </tr>
        </thead>
        <tbody>
    `;
  
    myRatings.forEach(r => {
      const user = users.find(u => u.email === r.user);
      html += `
        <tr>
          <td>${user?.name || "Unknown"}</td>
          <td>${user?.email}</td>
          <td>${r.rating}</td>
        </tr>
      `;
    });
  
    html += "</tbody></table>";
    document.getElementById("dashboard-content").innerHTML = html;
  }



  function showPasswordForm() {
    const content = document.getElementById("dashboard-content");
    content.innerHTML = `
      <h3>Change Password</h3>
      <input type="password" id="oldPass" placeholder="Current Password"><br>
      <input type="password" id="newPass" placeholder="New Password"><br>
      <input type="password" id="confirmPass" placeholder="Confirm New Password"><br>
      <button onclick="updatePassword()">Update</button>
    `;
  }

  

  function updatePassword() {
    const oldPass = document.getElementById("oldPass").value;
    const newPass = document.getElementById("newPass").value;
    const confirmPass = document.getElementById("confirmPass").value;
  
    const user = JSON.parse(localStorage.getItem("loggedInUser"));
    const users = getUsers();
    const index = users.findIndex(u => u.email === user.email);
  
    if (users[index].password !== oldPass) {
      alert("Current password is incorrect.");
      return;
    }
  
    if (newPass !== confirmPass) {
      alert("New passwords do not match.");
      return;
    }
  
    if (!validatePassword(newPass)) {
      alert("Password must be 8-16 characters, contain an uppercase letter and a special character.");
      return;
    }
  
    users[index].password = newPass;
    localStorage.setItem("users", JSON.stringify(users));
    alert("Password updated successfully!");
  
    // Reload dashboard
    loadDashboard();
  }

  

  function validatePassword(password) {
    const upper = /[A-Z]/.test(password);
    const special = /[^a-zA-Z0-9]/.test(password);
    return password.length >= 8 && password.length <= 16 && upper && special;
  }

  

  
  function loadAdminView() {
    const users = getUsers();
    const stores = JSON.parse(localStorage.getItem("stores")) || [];
  
    const filterHTML = `
      <div style="margin-bottom: 20px;">
        <input type="text" id="filterName" placeholder="Filter by Name">
        <input type="text" id="filterEmail" placeholder="Filter by Email">
        <input type="text" id="filterAddress" placeholder="Filter by Address">
        <select id="filterRole">
          <option value="">All Roles</option>
          <option value="user">User</option>
          <option value="admin">Admin</option>
          <option value="owner">Owner</option>
        </select>
        <button onclick="applyFilters()">Apply Filters</button>
      </div>
    `;
  
    const statsHTML = `
      <p>Total Users: ${users.length}</p>
      <p>Total Stores: ${stores.length}</p>
      <p>Total Ratings: ${getRatings().length}</p>
    `;
  
    const userTable = generateUserTable(users);
    const storeTable = generateStoreTable(stores);
  
    document.getElementById("dashboard-content").innerHTML = `
      ${filterHTML}
      ${statsHTML}
      <h4>User List</h4>
      ${userTable}
      <h4>Store List</h4>
      ${storeTable}
    `;
  }
  

  function applyFilters() {
    const name = document.getElementById("filterName").value.toLowerCase();
    const email = document.getElementById("filterEmail").value.toLowerCase();
    const address = document.getElementById("filterAddress").value.toLowerCase();
    const role = document.getElementById("filterRole").value;
  
    let users = getUsers();
    let stores = JSON.parse(localStorage.getItem("stores")) || [];
  
    if (name) users = users.filter(u => u.name.toLowerCase().includes(name));
    if (email) users = users.filter(u => u.email.toLowerCase().includes(email));
    if (address) users = users.filter(u => u.address.toLowerCase().includes(address));
    if (role) users = users.filter(u => u.role === role);
  
    document.getElementById("dashboard-content").innerHTML = `
      <button onclick="loadAdminView()">Reset Filters</button>
      ${generateUserTable(users)}
      ${generateStoreTable(stores)}
    `;
  }
  


  function generateUserTable(users) {
    let html = `
      <table>
        <thead>
          <tr>
            <th onclick="sortUsers('name')">Name ⬍</th>
            <th onclick="sortUsers('email')">Email ⬍</th>
            <th>Address</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
    `;
    users.forEach(user => {
      html += `
        <tr>
          <td>${user.name}</td>
          <td>${user.email}</td>
          <td>${user.address}</td>
          <td>${user.role}</td>
        </tr>
      `;
    });
    html += "</tbody></table>";
    return html;
  }

  
  let sortDirection = true; // ascending

function sortUsers(field) {
  const users = getUsers();
  users.sort((a, b) => {
    const valA = a[field].toLowerCase();
    const valB = b[field].toLowerCase();
    if (valA < valB) return sortDirection ? -1 : 1;
    if (valA > valB) return sortDirection ? 1 : -1;
    return 0;
  });
  sortDirection = !sortDirection;
  document.getElementById("dashboard-content").innerHTML = generateUserTable(users);
}


