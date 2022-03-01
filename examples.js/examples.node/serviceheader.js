'use strict';

const https = require('https');
const fs = require('fs');

function requestHandler(request, response) {
  const headers = {
    'Server-Timing': `
    cache;desc="Cache Read";dur=23.2,
    db;dur=53,
    app;dur=47.2
    `.replace(/\n/g, '')
  };
  response.writeHead(200, headers);
  response.write('');
  return setTimeout(_ => {
    response.end();
  }, 1000)
};

const server = https.createServer(requestHandler, { cert: fs.readFileSync('./fullchain9.pem'), key: fs.readFileSync('./privkey9.pem') });

server.listen(3000, () => {
  console.log("Listening on 3000");
});

server.on('open', () => console.log("OPEN"));

server.on('error', () => console.error);

server.on('request', (request) => { console.log("Request"); });