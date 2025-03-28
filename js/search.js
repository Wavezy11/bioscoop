document.addEventListener("DOMContentLoaded", () => {
  // API endpoints for fallback
  const localAPI = "http://localhost:3000/films"
  const remoteAPI = "https://project-bioscoop-restservice.azurewebsites.net/list/P76BWGQysAgp5rxw"

  // Use database movies if available
  let allMovies = [];

  async function fetchMovies() {
    // If we already have movies from the database, use those
    if (typeof databaseMovies !== 'undefined' && databaseMovies.length > 0) {
      allMovies = databaseMovies;
      return databaseMovies;
    }
    
    try {
      // Try local API first
      let response = await fetch(localAPI)
      let movies = await response.json()

      // If local API fails or returns empty, try remote API
      if (!movies || movies.length === 0) {
        response = await fetch(remoteAPI)
        movies = await response.json()
      }

      allMovies = movies
      return movies
    } catch (error) {
      console.error("Error fetching movies:", error)
      return []
    }
  }

  // Search functionality
  const searchInput = document.getElementById("search-input")
  const searchBtn = document.getElementById("search-btn")
  const searchResults = document.getElementById("search-results")

  function performSearch() {
    const query = searchInput.value.toLowerCase().trim()

    if (query === "") {
      searchResults.innerHTML = '<div class="no-results">Enter a search term to find movies</div>'
      return
    }

    const results = allMovies.filter(
      (movie) =>
        movie.title.toLowerCase().includes(query) ||
        (movie.category && movie.category.toLowerCase().includes(query)) ||
        (movie.description && movie.description.toLowerCase().includes(query)),
    )

    displaySearchResults(results)
  }

  function displaySearchResults(results) {
    searchResults.innerHTML = ""

    if (results.length === 0) {
      searchResults.innerHTML = '<div class="no-results">No movies found matching your search</div>'
      return
    }

    results.forEach((movie) => {
      const resultItem = document.createElement("div")
      resultItem.className = "search-result"
      resultItem.dataset.id = movie.id

      // Create a placeholder image data URI if no image is available
      const defaultPlaceholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZWVlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5OTk5Ij5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
      
      // Determine image URL
      let imageUrl = movie.image_url;
      
      resultItem.innerHTML = `
        <img src="${imageUrl || (typeof placeholderImage !== 'undefined' ? placeholderImage : defaultPlaceholder)}" 
             alt="${movie.title}" class="result-poster"
             onerror="this.onerror=null; this.src='${typeof placeholderImage !== 'undefined' ? placeholderImage : defaultPlaceholder}'">
        <div class="result-info">
          <h3 class="result-title">${movie.title}</h3>
          <p class="result-genre">${movie.category || "Geen categorie"}</p>
          <span class="result-rating">${movie.rating || "4.0"}</span>
        </div>
      `

      resultItem.addEventListener("click", () => {
        localStorage.setItem("selectedMovie", JSON.stringify(movie))
        window.location.href = `movie-details.php?id=${movie.id}`
      })

      searchResults.appendChild(resultItem)
    })
  }

  // Add event listeners
  searchBtn.addEventListener("click", performSearch)
  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      performSearch()
    }
  })

  // Initialize
  fetchMovies().then(() => {
    searchResults.innerHTML = '<div class="no-results">Enter a search term to find movies</div>'
  })
})