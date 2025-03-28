const express = require('express');
const bodyParser = require('body-parser');
const mysql = require('mysql2');
const app = express();
const PORT = 3000;

// Configuratie voor MySQL database
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'films'
});

db.connect(err => {
  if (err) {
    console.error('Database connectie mislukt:', err);
  } else {
    console.log('Database verbonden');
  }
});

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Voeg film toe via API en Database
app.post('/add', async (req, res) => {
  const filmData = req.body;
  
  try {
    // Film naar de database sturen
    const { title, description, category, url_trailer } = filmData;
    const query = `INSERT INTO films (title, description, category, url_trailer) VALUES (?, ?, ?, ?)`;
    
    db.query(query, [title, description, category, url_trailer], (err, dbResult) => {
      if (err) {
        return res.status(500).json({ message: 'Fout bij het toevoegen aan de database', err });
      }
      res.status(200).json({ message: 'Film succesvol toegevoegd aan de database' });
    });
  } catch (error) {
    console.error('Fout bij het verzenden van gegevens:', error);
    res.status(500).json({ message: 'Er is een fout opgetreden' });
  }
});

// Haal alle films op voor het dashboard
app.get('/films', (req, res) => {
  db.query('SELECT * FROM films', (err, result) => {
    if (err) {
      return res.status(500).json({ message: 'Fout bij het ophalen van films' });
    }
    res.json(result);
  });
});

// Gebruikers beheren (admin acties)
app.get('/users', (req, res) => {
  db.query('SELECT * FROM users', (err, result) => {
    if (err) {
      return res.status(500).json({ message: 'Fout bij het ophalen van gebruikers' });
    }
    res.json(result);
  });
});

// Maak gebruiker admin
app.post('/makeAdmin', (req, res) => {
  const userId = req.body.userId;
  db.query('UPDATE users SET is_admin = 1 WHERE id = ?', [userId], (err, result) => {
    if (err) {
      return res.status(500).json({ message: 'Fout bij het maken van gebruiker admin' });
    }
    res.json({ message: 'Gebruiker succesvol admin gemaakt' });
  });
});

// Verwijder gebruiker
app.delete('/deleteUser', (req, res) => {
  const userId = req.body.userId;
  db.query('DELETE FROM users WHERE id = ?', [userId], (err, result) => {
    if (err) {
      return res.status(500).json({ message: 'Fout bij het verwijderen van gebruiker' });
    }
    res.json({ message: 'Gebruiker succesvol verwijderd' });
  });
});

// Server starten
app.listen(PORT, () => {
  console.log(`Server draait op poort ${PORT}`);
});
