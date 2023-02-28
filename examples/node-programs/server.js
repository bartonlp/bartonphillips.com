// Node https server using express

const https = require("https");
const express = require("express");
const bodyParser = require("body-parser");
const fs = require("fs");
const cors = require("cors");

// New app using express module

const app = express();
app.use(bodyParser.urlencoded({
  extended:true
}));

// Set up CORS for anyone.

app.use(cors({
  origin: '*'
}));

// This get wants a url like
// "https://bartonphillips.com:3000/<some value>/<some other value>"
// These will be put in the name and test params object.

app.get("/:name/:test", (req, res)=> {
  let name = req.params.name;
  let test = req.params.test;
  //let test = req.body.test;
  console.log(`GET: ${name}, ${test}`); // ${name}, ${test}`);
  res.sendFile(__dirname + "/index.html");
});

apt.get("test/:id", (req, res)=> {
  let name = req.params.id;
  res.sendFile(__dirname + "/test1.php");
});

// This get wants two query items like
// "https://bartonphillips.com:3000/?name=<some value>&test=<some other value">
// Again these are put into the query object as name and test.

app.get("/", (req, res)=> {
  let name = req.query.name;
  let test = req.query.test;
  console.log(`GET query: ${name}, ${test}`);
  res.send(`<h1>This is GET data: ${name}, ${test}</h1>`);
});

// This is a POST to
// "https://bartonphillips.com:3000/getit"
// The body object has the name and test.

app.post("/getit", (req, res)=> {
  let name = req.body.name;
  let test = req.body.test;
  console.log(`Sent from client: ${name}, ${test}`);
  var result = `${name} ${test}`;
  console.log(`Sending back: ${result}`);
  res.send(`Hi From Server: ${result}`)
});

const PORT = 3000;

// We create the server with our certs.

https.createServer({
  cert: fs.readFileSync('/etc/letsencrypt/live/www.bartonphillips.com/fullchain.pem'),
  key: fs.readFileSync('/etc/letsencrypt/live/www.bartonphillips.com/privkey.pem')
}, app)
.listen(PORT, () => {
  console.log(`serever is runing at port ${PORT}`);
});

