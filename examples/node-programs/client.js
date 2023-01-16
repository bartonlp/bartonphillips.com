// This is a node program
// This is the client to the server.js.

const https = require('https');

let request = https.get('https://bartonphillips.com:3000?name=Barton&test=Great One', (res) => {
  if (res.statusCode !== 200) {
    console.error(`Did not get an OK from the server. Code: ${res.statusCode}`);
    res.resume();
    return;
  }

  let data = '';

  res.on('data', (chunk) => {
    data += chunk;
  });

  res.on('close', () => {
    console.log('Retrieved all data');
    console.log(`From Server: ${data}`);
  });
});
request.on('error', (err) => {
  console.error(`Encountered an error trying to make a request: ${err.message}`);
});
