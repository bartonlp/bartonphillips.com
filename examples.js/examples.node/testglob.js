// https://github.com/isaacs/node-glob
var glob = require('glob'); //.Glob;
var path = require('path');

/*var pattern = "*.php";
console.log(pattern);
var mg = new Glob(pattern, {mark: true}, function (er, matches) {
  console.log("matches", matches);
});
console.log("after");
*/

let pattern = "/var/www/bartonphillipsnet/images/Bonnie/*.png";
let match = glob.sync(pattern);
console.log(match);
const match_iter = match[Symbol.iterator] = ;
console.log(match_iter);

