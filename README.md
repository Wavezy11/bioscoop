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
2. Installeer de benodigde dependencies:
