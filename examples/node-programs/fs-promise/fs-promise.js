// using fs-extra to promisfy fs.

const fs = require('fs-extra');

fs.readFile("fs-promise.js", "utf8")
.then(d=>console.log("promise:", d))
.catch(err=>console.log('err:', err));

fs.readFile("fs-promise.js", "utf8", (err, d)=>{
  if(err) {
    console.log(err);
    return;
  }
  console.log("callback:", d);
});

try {
  let d = fs.readFileSync("fs-promise.js", "utf8");
  console.log("sync:", d);
} catch(err) {
  console.log(err);
}

fs.readJson("../package.json", "utf8")
.then(d=>console.log("json:", d))
.catch(err=>console.log(err));

