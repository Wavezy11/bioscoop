/**
 * Haalt films op van de remote server.
 * @param {string} remoteServer - De URL van de remote server.
 * @param {string} apiKey - De API-sleutel.
 * @returns {Promise<any>} - De response van de server in JSON-formaat.
 */
const getFilms = async (remoteServer, apiKey) => {
  const response = await fetch(`${remoteServer}/list/${apiKey}`);
  return response.json();
};

/**
* Haalt de details van een specifieke film op.
* @param {string} remoteServer - De URL van de remote server.
* @param {string} apiKey - De API-sleutel.
* @param {string} idFilm - Het ID van de film.
* @returns {Promise<any>} - De details van de film in JSON-formaat.
*/
const getFilmDetails = async (remoteServer, apiKey, idFilm) => {
  const response = await fetch(`${remoteServer}/details/${idFilm}/${apiKey}`, {
      method: 'GET',
      headers: {
          'Content-Type': 'application/json'
      },
  });
  return response.json();
};

/**
* Verstuur filmgegevens naar de remote server om een film toe te voegen.
* @param {string} remoteServer - De URL van de remote server.
* @param {string} apiKey - De API-sleutel.
* @param {Object} data - De gegevens van de film om te versturen.
* @returns {Promise<any>} - De response van de server in JSON-formaat.
*/
const postFilmData = async (remoteServer, apiKey, data) => {
  data.apiKey = apiKey;  // Voeg de API-sleutel toe aan de data

  const response = await fetch(`${remoteServer}/add/${apiKey}`, {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)  // Verstuur de data als JSON
  });

  return response.json();  // Retourneer de parsed JSON response
};

/**
* Verhoogt het aantal stemmen voor een film.
* @param {string} remoteServer - De URL van de remote server.
* @param {string} apiKey - De API-sleutel.
* @param {string} idFilm - Het ID van de film.
* @returns {Promise<any>} - De response van de server in JSON-formaat.
*/
const voteUp = async (remoteServer, apiKey, idFilm) => {
  const response = await fetch(`${remoteServer}/voteup/${idFilm}/${apiKey}`, {
      method: 'PUT',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({})  // Geen gegevens, maar body moet niet leeg zijn
  });

  return response.json();  // Retourneer de parsed JSON response
};

/**
* Verwijdert een film van de remote server.
* @param {string} remoteServer - De URL van de remote server.
* @param {string} apiKey - De API-sleutel.
* @param {string} idFilm - Het ID van de film.
* @returns {Promise<any>} - De response van de server in JSON-formaat.
*/
const deleteFilm = async (remoteServer, apiKey, idFilm) => {
  const response = await fetch(`${remoteServer}/delete/${idFilm}/${apiKey}`, {
      method: 'DELETE',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({})  // Body is meestal leeg bij DELETE, maar je moet het nog steeds doorgeven
  });

  return response.json();  // Retourneer de parsed JSON response
};

// Exporteer de functies zodat ze in andere bestanden gebruikt kunnen worden
module.exports = { getFilms, getFilmDetails, postFilmData, voteUp, deleteFilm };
