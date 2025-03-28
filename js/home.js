document.addEventListener("DOMContentLoaded", () => {
  // API endpoints
  const localAPI = "http://localhost:3000/films"
  const remoteAPI = "https://project-bioscoop-restservice.azurewebsites.net/list/P76BWGQysAgp5rxw"

  // Fetch movies from API if database movies aren't available
  async function fetchMovies() {
    try {
      // First use the database movies passed from PHP
      if (typeof databaseMovies !== 'undefined' && databaseMovies.length > 0) {
        return databaseMovies;
      }
      
      // If no database movies, try local API
      let response = await fetch(localAPI)
      let movies = await response.json()

      // If local API fails or returns empty, try remote API
      if (!movies || movies.length === 0) {
        response = await fetch(remoteAPI)
        movies = await response.json()
      }

      return movies
    } catch (error) {
      console.error("Error fetching movies:", error)
      return []
    }
  }

  // Filter movies by genre
  function filterMoviesByGenre(movies, genre) {
    if (genre === "action") {
      return movies.filter((movie) => movie.category && movie.category.toLowerCase().includes("action"))
    } else if (genre === "comedy") {
      return movies.filter((movie) => movie.category && movie.category.toLowerCase().includes("comedy"))
    } else if (genre === "scify") {
      return movies.filter((movie) => movie.category && movie.category.toLowerCase().includes("scify"))
    }
    return movies
  }

  // Display movies in the container
  function displayMovies(movies) {
    const moviesContainer = document.querySelector(".movies-container")
    moviesContainer.innerHTML = ""

    if (movies.length === 0) {
      moviesContainer.innerHTML = '<p class="no-movies">Geen films gevonden in deze categorie</p>'
      return
    }

    movies.forEach((movie) => {
      const movieCard = document.createElement("div")
      movieCard.className = "movie-card"
      movieCard.dataset.id = movie.id

      // Determine image URL (handle both local and remote paths)
      let imageUrl = movie.image_url
      if (imageUrl && !imageUrl.startsWith("http")) {
        // Check if it's a relative path from database
        if (!imageUrl.includes('localhost')) {
          imageUrl = imageUrl; // Use as is, it's a relative path
        } else {
          imageUrl = `http://localhost:3000${imageUrl}`
        }
      }

      // Create a placeholder image data URI if no image is available
      const placeholderImage = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZWVlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5OTk5Ij5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';

      movieCard.innerHTML = `
        <img src="${imageUrl || placeholderImage}" alt="${movie.title}" class="movie-poster" onerror="this.onerror=null; this.src='${placeholderImage}'">
        <div class="movie-info">
          <h3 class="movie-title">${movie.title}</h3>
          <p class="movie-genre">${movie.category || "Geen categorie"}</p>
          <span class="movie-rating">${movie.rating || "4.0"}</span>
        </div>
      `

      movieCard.addEventListener("click", () => {
        localStorage.setItem("selectedMovie", JSON.stringify(movie))
        window.location.href = "movie-details.php"
      })

      moviesContainer.appendChild(movieCard)
    })
  }

  // Initialize with all movies
  async function initializeMovies() {
    const movies = await fetchMovies()

    // Initially show all movies
    displayMovies(movies)

    // Add event listeners to genre tabs
    const genreTabs = document.querySelectorAll(".genre-tab")
    genreTabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        // Remove active class from all tabs
        genreTabs.forEach((t) => t.classList.remove("active"))
        // Add active class to clicked tab
        tab.classList.add("active")

        // Filter and display movies
        const genre = tab.dataset.genre
        const filteredMovies = filterMoviesByGenre(movies, genre)
        displayMovies(filteredMovies)
      })
    })
  }

  // Start loading movies
  initializeMovies()
})