const express = require("express")
const cors = require("cors")
const multer = require("multer")
const path = require("path")
const mysql = require("mysql2")
const fetch = require("node-fetch")

const app = express()
app.use(cors())
app.use(express.json())
app.use(express.static("public"))

// Multer configuration
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, "public/images/db")
  },
  filename: (req, file, cb) => {
    const filename = Date.now() + path.extname(file.originalname)
    cb(null, filename)
  },
})
const upload = multer({ storage: storage })

// MySQL connection
const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "films",
})

db.connect((err) => {
  if (err) {
    console.error("Error connecting to database:", err)
    return
  }
  console.log("Connected to MySQL database")
})

// Function to send film data to external API
const postFilmData = async (remoteServer, apiKey, data = {}) => {
  console.log("Sending data to API:", JSON.stringify(data))
  try {
    const response = await fetch(`${remoteServer}/list/${apiKey}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
    console.log("API Response Status:", response.status)
    const responseText = await response.text()
    console.log("API Response Text:", responseText)
    try {
      return JSON.parse(responseText)
    } catch (jsonError) {
      console.error("Error parsing JSON:", jsonError)
      return { success: false, message: responseText }
    }
  } catch (error) {
    console.error("Error in postFilmData:", error)
    throw error
  }
}


app.post("/upload", upload.single("image"), async (req, res) => {
  console.log("Received upload request")
  console.log("Request body:", req.body)
  console.log("File:", req.file)

  try {
    const { title, description, category, url_trailer } = req.body
    if (!req.file) {
      throw new Error("No image file received")
    }
    const image_url = `/images/${req.file.filename}`

    console.log("Processed data:", { title, description, category, url_trailer, image_url })

    // Add the film to the MySQL database
    const query = "INSERT INTO films (title, description, category, url_trailer, image_url) VALUES (?, ?, ?, ?, ?)"
    db.query(query, [title, description, category, url_trailer, image_url], async (dbErr, result) => {
      if (dbErr) {
        console.error("Error adding film to database:", dbErr)
        return res.status(500).json({ success: false, message: "Error adding the film to the database." })
      }

      console.log("Film added to database")
      const filmId = result.insertId

      try {
        const apiKey = "P76BWGQysAgp5rxw"
        const remoteServer = "https://project-bioscoop-restservice.azurewebsites.net"

        const apiData = {
          title,
          description,
          category,
          url_trailer,
          image_url: `${req.protocol}://${req.get("host")}${image_url}`,
        }

        console.log("Sending to API:", `${remoteServer}/list/${apiKey}`)
        console.log("API Data:", JSON.stringify(apiData))

        const apiResult = await postFilmData(remoteServer, apiKey, apiData)
        console.log("API response:", apiResult)

        if (apiResult.success) {
          res.status(200).json({ success: true, message: "Film successfully added to database and API!" })
        } else {
          throw new Error(apiResult.message || "Unknown error adding to API")
        }
      } catch (apiError) {
        console.error("Error sending to external API:", apiError)
        res.status(500).json({
          success: false,
          message: "Film added to database, but error adding to the external API.",
          error: apiError.toString(),
        })
      }
    })
  } catch (error) {
    console.error("Unexpected error:", error)
    res.status(500).json({ success: false, message: "An unexpected error occurred.", error: error.toString() })
  }
})


// Express route om cast-leden op te halen op basis van film_id
app.get('/api/cast/:film_id', (req, res) => {
  const filmId = req.params.film_id;
  const query = `SELECT * FROM cast_members WHERE film_id = ?`;
  db.query(query, [filmId], (err, results) => {
      if (err) {
          return res.status(500).json({ error: 'Database error' });
      }
      res.json(results);
  });
});



app.get("/films", (req, res) => {
  db.query("SELECT * FROM films", (err, result) => {
    if (err) {
      console.error("Error fetching films from the database:", err)
      return res.status(500).json({ success: false, message: "Error fetching films" })
    }
    res.json(result)
  })
})


app.post('/add', async (req, res) => {
  const filmData = req.body;
  const remoteServer = 'https://project-bioscoop-restservice.azurewebsites.net';
  const apiKey = 'P76BWGQysAgp5rxw';
  const insertPhpUrl = 'http://localhost/insert.php';  // Je PHP-bestand dat de database invoert

  console.log(filmData);

  try {
      // Verstuur de gegevens naar de externe server
      const response = await fetch(`${remoteServer}/add/${apiKey}`, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify(filmData),  // Verstuur de filmdata als JSON
      });

      const result = await response.json();
      console.log(result);

      if (response.ok) {
          // Nu de film ook naar de database sturen via PHP
          const dbResponse = await fetch(insertPhpUrl, {
              method: "POST",
              headers: {
                  "Content-Type": "application/x-www-form-urlencoded",
              },
              body: new URLSearchParams(filmData)  // Verstuur de gegevens als x-www-form-urlencoded
          });

          const dbResult = await dbResponse.json();
          if (dbResponse.ok) {
              res.status(200).json({ message: 'Film succesvol toegevoegd aan API en database', result, dbResult });
          } else {
              res.status(500).json({ message: 'Fout bij het toevoegen van de film aan de database', dbResult });
          }
      } else {
          res.status(400).json({ message: 'Fout bij het toevoegen van de film aan de API', result });
      }
  } catch (error) {
      console.error('Fout bij het verzenden van gegevens:', error);
      res.status(500).json({ message: 'Er is een fout opgetreden bij het verzenden van gegevens' });
  }
});
app.get('/add', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'insert.php'));  // Stuur een formulierpagina naar de client
});


app.use((err, req, res, next) => {
  console.error("Unhandled error:", err)
  res.status(500).json({ success: false, message: "An unexpected error occurred.", error: err.toString() })
})

app.get("/upload", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "upload.html"))
})

const PORT = process.env.PORT || 3000
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`)
})

