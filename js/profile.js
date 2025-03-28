document.addEventListener("DOMContentLoaded", () => {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem("isLoggedIn")
    if (!isLoggedIn || isLoggedIn !== "true") {
      window.location.href = "index.php"
      return
    }
  
    // Get user information
    const userEmail = localStorage.getItem("userEmail") || "User"
    const userId = localStorage.getItem("userId") || null
  
    // Update username in the UI (even though it's hidden)
    document.getElementById("user-name").textContent = userEmail
  
    // API endpoints
    const localAPI = "http://localhost:3000/films"
    const remoteAPI = "https://project-bioscoop-restservice.azurewebsites.net/list/P76BWGQysAgp5rxw"
    const apiKey = "P76BWGQysAgp5rxw"
  
    // Get user's voting history from localStorage
    const votes = JSON.parse(localStorage.getItem("votes") || "[]")
  
    // Update the vote count in the UI (even though it's hidden)
    document.getElementById("votes-count").textContent = votes.length
  
    // Function to vote for a movie in both local DB and remote API
    async function voteForMovie(movieId, movieApiId) {
      try {
        // First, try to vote in the local database
        const localResponse = await fetch(`http://localhost:3000/api/films/${movieId}/vote`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
        })
  
        if (!localResponse.ok) {
          console.warn("Failed to register vote in local database")
          throw new Error("Local vote failed")
        }
  
        const localResult = await localResponse.json()
        console.log("Local vote successful:", localResult)
  
        // Then, try to vote in the remote API if we have an API ID
        if (movieApiId) {
          const remoteResponse = await fetch(`https://project-bioscoop-restservice.azurewebsites.net/vote/${apiKey}`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              id: movieApiId,
              apikey: apiKey,
            }),
          })
  
          if (!remoteResponse.ok) {
            console.warn("Failed to register vote in remote API")
            // We continue even if remote vote fails
          } else {
            const remoteResult = await remoteResponse.json()
            console.log("Remote vote successful:", remoteResult)
          }
        }
  
        return true
      } catch (error) {
        console.error("Error voting for movie:", error)
        return false
      }
    }
  
    // Function to fetch movies for voting
    async function fetchMoviesForVoting() {
      try {
        // First try to get movies from local API
        let response = await fetch(localAPI)
        let movies = []
  
        if (response.ok) {
          movies = await response.json()
        }
  
        // If no movies from local API, try remote API
        if (!movies || movies.length === 0) {
          response = await fetch(remoteAPI)
          if (response.ok) {
            movies = await response.json()
          }
        }
  
        // Return the first 9 movies for voting
        return movies.slice(0, 9)
      } catch (error) {
        console.error("Error fetching movies for voting:", error)
        return []
      }
    }
  
    // Function to display voting options
    async function displayVotingOptions() {
      const votingContainer = document.getElementById("voting-container")
      votingContainer.innerHTML = "<p>Loading movies...</p>"
  
      const votingMovies = await fetchMoviesForVoting()
      votingContainer.innerHTML = ""
  
      if (votingMovies.length === 0) {
        votingContainer.innerHTML = "<p>Geen films beschikbaar om op te stemmen</p>"
        return
      }
  
      votingMovies.forEach((movie) => {
        const voteCard = document.createElement("div")
        voteCard.className = "vote-card"
        voteCard.dataset.id = movie.id
        voteCard.dataset.apiId = movie._id || "" // Store API ID if available
  
        // Handle image URL
        let imageUrl = movie.image_url
        if (imageUrl && !imageUrl.startsWith("http") && !imageUrl.startsWith("data:")) {
          // Make sure the path starts with a slash
          if (!imageUrl.startsWith("/")) {
            imageUrl = `/${imageUrl}`
          }
          imageUrl = `http://localhost:3000${imageUrl}`
        }
  
        // Check if user has already voted for this movie
        const hasVoted = votes.some((vote) => vote.id === movie.id.toString())
  
        voteCard.innerHTML = `
          <img src="${imageUrl || "/placeholder.svg?height=200&width=150"}" alt="${movie.title}" class="vote-poster" onerror="this.src='/placeholder.svg?height=200&width=150'">
          <div class="vote-info">
            <h3 class="vote-title">${movie.title}</h3>
            <p class="vote-count">Votes: ${movie.votes || 0}</p>
            <button class="vote-btn ${hasVoted ? "voted" : ""}">${hasVoted ? "Voted" : "Stem"}</button>
          </div>
        `
  
        votingContainer.appendChild(voteCard)
      })
  
      // Add event listeners to vote buttons
      const voteButtons = document.querySelectorAll(".vote-btn")
      voteButtons.forEach((button) => {
        button.addEventListener("click", async function (e) {
          e.preventDefault()
          e.stopPropagation()
  
          // Disable button during voting process
          this.disabled = true
          this.textContent = "Voting..."
  
          const voteCard = this.closest(".vote-card")
          const movieId = voteCard.dataset.id
          const movieApiId = voteCard.dataset.apiId
          const movieTitle = voteCard.querySelector(".vote-title").textContent
          const voteCountElement = voteCard.querySelector(".vote-count")
          const currentVotes = Number.parseInt(voteCountElement.textContent.split(": ")[1])
  
          // Check if user has already voted for this movie
          const existingVoteIndex = votes.findIndex((vote) => vote.id === movieId)
  
          if (existingVoteIndex === -1) {
            // Add vote
            const success = await voteForMovie(movieId, movieApiId)
  
            if (success) {
              // Update UI
              votes.push({ id: movieId, title: movieTitle })
              this.textContent = "Voted"
              this.classList.add("voted")
  
              // Update vote count in UI
              voteCountElement.textContent = `Votes: ${currentVotes + 1}`
  
              // Update localStorage
              localStorage.setItem("votes", JSON.stringify(votes))
              document.getElementById("votes-count").textContent = votes.length
            } else {
              // Voting failed
              this.textContent = "Failed"
              setTimeout(() => {
                this.textContent = "Stem"
                this.disabled = false
              }, 2000)
            }
          } else {
            // We don't allow removing votes in this implementation
            this.textContent = "Already voted"
            setTimeout(() => {
              this.textContent = "Voted"
              this.disabled = false
            }, 2000)
          }
        })
      })
    }
  
    // Initialize voting section
    displayVotingOptions()
  })
  
  