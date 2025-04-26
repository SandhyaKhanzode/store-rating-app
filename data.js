if (!localStorage.getItem("users")) {
    localStorage.setItem("users", JSON.stringify([
      {
        name: "Admin System",
        email: "admin@site.com",
        password: "Admin@123",
        address: "HQ",
        role: "admin"
      }
    ]));
    localStorage.setItem("stores", JSON.stringify([]));
    localStorage.setItem("ratings", JSON.stringify([]));
  }
  