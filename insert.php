<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voeg Film Toe</title>
</head>
<body>
    <h1>Voeg een Film Toe</h1>

    <form id="filmForm" action="/insert" method="POST">
        <label for="title">Titel:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Beschrijving:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="url_trailer">Trailer URL:</label>
        <input type="url" id="url_trailer" name="url_trailer" required><br><br>

        <label for="category">Categorie:</label>
        <input type="text" id="category" name="category" required><br><br>

        <input type="hidden" name="apikey" value="P76BWGQysAgp5rxw">
        <button type="submit">Voeg Film Toe</button>
    </form>

    <ul id="filmsList">
      
    </ul>
    <script>
        const restService = 'https://project-bioscoop-restservice.azurewebsites.net';
        const apiKey = 'P76BWGQysAgp5rxw';

    
        document.getElementById("filmForm").addEventListener("submit", async (event) => {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData);
        console.log(formData);
        try {
            const response = await fetch(`${restService}/add/${apiKey}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                const result = await response.json();
                console.log("Film succesvol toegevoegd:", result);
                alert(result.message);
                await getFilms();
            } else {
                const result = await response.json();
                console.error("Fout bij toevoegen van film:", result);
                alert(result.message);
            }
        } catch (error) {
            console.error("Fout bij het verzenden van gegevens:", error);
            alert("Er is een fout opgetreden!");
        }
    });


        // Functie om films op te halen en weer te geven
        const getFilms = async () => {
            try {
                const response = await fetch(`${restService}/list/${apiKey}`);
                const films = await response.json();

                const filmsList = document.getElementById('filmsList');
                filmsList.innerHTML = ''; // Leeg de lijst voordat je nieuwe films toevoegt

                films.forEach(film => {
                    const listItem = document.createElement('li');
                    listItem.textContent = `${film.title} - ${film.category}`;
                    filmsList.appendChild(listItem);
                });
            } catch (error) {
                console.error('Fout bij het ophalen van films:', error);
            }
        };

        // Haal de films op bij het laden van de pagina
        window.onload = getFilms;
    </script>
</body>
</html>
