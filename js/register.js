document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.querySelector(".login-form")

  if (registerForm) {
    registerForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const name = document.getElementById("name").value
      const email = document.getElementById("email").value
      const password = document.getElementById("password").value
      const confirmPassword = document.getElementById("confirm-password").value

      // Validate passwords match
      if (password !== confirmPassword) {
        alert("Passwords do not match!")
        return
      }

      // Normally this would be handled by the PHP script
      // This is just for demo purposes
      console.log("Register attempt:", name, email, password)

      // Simulate registration success
      // In a real app, this would be handled by the PHP response
      localStorage.setItem("isLoggedIn", "true")
      localStorage.setItem("userEmail", email)
      localStorage.setItem("userName", name)

      // Redirect to home page
      window.location.href = "home.php"
    })
  }
})

