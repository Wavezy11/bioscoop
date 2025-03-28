# Film Management System

Een webapplicatie voor het beheren van films, cast leden en gebruikers.

## Projectbeschrijving

Dit project is een filmbeheerssysteem dat het mogelijk maakt om:
- Films toe te voegen, te bekijken en te beheren
- Cast leden aan films te koppelen
- Gebruikers te beheren (inclusief admin-rechten)
- Films te uploaden via een externe API en deze te koppelen aan de lokale database

## Database Structuur

Het systeem maakt gebruik van een MySQL database met de volgende tabellen:

### Films
Bevat informatie over films zoals titel, beschrijving, categorie, trailer URL en afbeelding URL.

### Cast Members
Bevat informatie over acteurs/actrices die in films spelen, gekoppeld aan de film via `film_id`.

### Users
Bevat gebruikersinformatie inclusief gebruikersnaam, wachtwoord en admin-status.

### Votes
Houdt bij welke gebruikers op welke films hebben gestemd.

## Technische Stack

- **Backend**: Node.js met Express
- **Database**: MySQL
- **API Integratie**: Externe film API voor het ophalen van filmgegevens

## Installatie

1. Clone de repository
2. Installeer de benodigde dependencies: npm init -y,  npm install express body-parser mysql2 
4. Importeer de database structuur uit het SQL bestand
5. Configureer de database verbinding in `server.js` en zorg dat de gebruikersnaam root is en de database films heet!!
6. Start de server:


## API Endpoints

### Films

- `GET /films` - Haal alle films op
- `POST /add` - Voeg een nieuwe film toe

### Gebruikers

- `GET /users` - Haal alle gebruikers op
- `POST /makeAdmin` - Maak een gebruiker admin
- `DELETE /deleteUser` - Verwijder een gebruiker

## Externe API Integratie

Het systeem maakt gebruik van een externe API om filmgegevens op te halen en te synchroniseren met de lokale database. De API-sleutel wordt gebruikt voor authenticatie.

API Base URL: `https://project-bioscoop-restservice.azurewebsites.net`

## Functionaliteiten

- Beheer van films (toevoegen, bekijken, bewerken, verwijderen)
- Beheer van gebruikers (toevoegen, bekijken, admin-rechten toekennen, verwijderen)
- Koppelen van cast leden aan films
- Stemmen op films door gebruikers
- Integratie met externe film API

## Beveiliging

- Wachtwoorden worden gehasht opgeslagen in de database
- Admin-rechten zijn vereist voor bepaalde acties
- API-sleutel authenticatie voor externe API-verzoeken

## Toekomstige Ontwikkelingen

- Verbeterde zoekfunctionaliteit
- Gebruikersbeoordelingen en recensies
- Geavanceerde filteropties voor films
- Mobiele app integratie
