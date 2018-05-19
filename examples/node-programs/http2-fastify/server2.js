const express = require('express');
const app = express();

app.get('/', (req, res, next) => res.send("hello World"));
app.listen(3000, () => console.log("listening on 3000"));
