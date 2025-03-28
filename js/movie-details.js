document.addEventListener("DOMContentLoaded", () => {
  // Get movie data - first try from PHP database, then from localStorage
  let selectedMovie = databaseMovie;
  let castMembers = databaseCast;
  
  // If no database movie, try localStorage
  if (!selectedMovie) {
    selectedMovie = JSON.parse(localStorage.getItem("selectedMovie"));
    
    // If we have a movie from localStorage but no ID in URL, add it
    if (selectedMovie && !window.location.href.includes('?id=')) {
      // Redirect to the same page with ID parameter
      window.location.href = `movie-details.php?id=${selectedMovie.id}`;
      return;
    }
  }

  if (!selectedMovie) {
    window.location.href = "home.php";
    return;
  }

  // Set the movie details
  document.title = `Democratische-bioscoop - ${selectedMovie.title}`;
  document.getElementById("movie-title").textContent = selectedMovie.title;
  document.getElementById("movie-type").textContent = "Movie";
  document.getElementById("movie-genres").textContent = selectedMovie.category || "Geen categorie";
  document.getElementById("movie-rating").textContent = selectedMovie.rating || "4.0";
  document.getElementById("movie-description").textContent =
    selectedMovie.description || "Geen beschrijving beschikbaar.";

  // Set background image for the movie
  const movieBackdrop = document.querySelector(".movie-backdrop");
  let imageUrl = selectedMovie.image_url;
  
  // Handle different image path formats
  if (imageUrl) {
    if (!imageUrl.startsWith("http")) {
      // It's a relative path, use as is
      imageUrl = imageUrl;
    }
  } else {
    imageUrl = placeholderImage;
  }

  movieBackdrop.style.backgroundImage = `url('${imageUrl}')`;

  // Display cast members if not already displayed by PHP
  const castList = document.getElementById("cast-list");
  
  // Only try to fetch cast if we don't already have it from PHP
  if (castList.children.length === 0) {
    // If we have cast from JavaScript variable, use it
    if (castMembers && castMembers.length > 0) {
      displayCastMembers(castMembers);
    } else if (selectedMovie.id) {
      // Try to fetch cast from database via a new PHP endpoint
      fetch(`get-cast.php?film_id=${selectedMovie.id}`)
        .then(response => response.json())
        .then(data => {
          if (data && data.length > 0) {
            displayCastMembers(data);
          } else {
            castList.innerHTML = "<p>Geen cast informatie beschikbaar</p>";
          }
        })
        .catch(error => {
          console.error("Error fetching cast:", error);
          castList.innerHTML = "<p>Er is een fout opgetreden bij het ophalen van de castinformatie.</p>";
        });
    } else {
      castList.innerHTML = "<p>Geen cast informatie beschikbaar</p>";
    }
  }

  // Function to display cast members
  function displayCastMembers(cast) {
    const castContainer = document.createElement("div");
    castContainer.className = "cast-list";

    cast.forEach((actor) => {
      const castItem = document.createElement("div");
      castItem.className = "cast-item";

      // Handle image path
      let actorImageUrl = actor.image_url;
      if (!actorImageUrl) {
        actorImageUrl = placeholderImage;
      }

      castItem.innerHTML = `
        <img src="${actorImageUrl}" alt="${actor.name}" class="cast-photo" 
             onerror="this.onerror=null; this.src='${placeholderImage}'">
        <p class="cast-name">${actor.name}</p>
        <p class="character-name">${actor.character_name || ''}</p>
      `;

      castContainer.appendChild(castItem);
    });

    castList.innerHTML = '';
    castList.appendChild(castContainer);
  }

  // Watch button functionality
  const watchBtn = document.querySelector(".watch-btn");
  watchBtn.addEventListener("click", () => {
    if (selectedMovie.url_trailer) {
      window.open(selectedMovie.url_trailer, "_blank");
    } else {
      alert(`Starting playback for ${selectedMovie.title}`);
    }

    // Track watched movies in localStorage
    const watchedMovies = JSON.parse(localStorage.getItem("watchedMovies") || "[]");

    if (!watchedMovies.some((movie) => movie.id === selectedMovie.id)) {
      watchedMovies.push(selectedMovie);
      localStorage.setItem("watchedMovies", JSON.stringify(watchedMovies));
    }
  });
});