// Use nodejs
// Memorize a function. This is caching a function. Which works well
// for recursive functions where you would need the revious values to
// make the function run faster. A factorial for example.

const memoize = (fn) => {
  let cache = {};
  return (...args) => {
    let n = args[0];
    if(n in cache) {
      console.log('Fetching from cache', n);
      return cache[n];
    } else {
      console.log('Calculating result', n);
      let result = fn(n);
      cache[n] = result;
      return result;
    }
  }
}

/*
const factorial = memoize(fact);

function fact(x) {
  if (x === 0) {
    return 1;
  } else {
    return x * factorial(x - 1); // if this refered back to 'fact' this
                                 // will not work!
  }
};
*/
  
const factorial = memoize((x) => {
  if (x === 0) {
    return 1;
  } else {
    return x * factorial(x - 1);
  }
});

console.log(factorial(5)); // calculated
console.log(factorial(6)); // calculated for 6 and cached for 5
console.log(factorial(10));
console.log(factorial(12));
console.log(factorial(3));

