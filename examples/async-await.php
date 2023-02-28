<?php
// async-await.php
// This demonstrates the use of an 'async function' and await. It also has a 'generator' that
// uses [Symbol.iterator] to let us use 'for of'.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

$S->banner = "<h1>async-await.php</h1>";
$S->css =<<<EOF
#div {
  font-size: 1.5rem;
}
EOF;

$S->b_inlineScript =<<<EOF
function delayIt(x) {
  return new Promise(res => {
    setTimeout(() => {
      res(x);
    }, 2000);
  });
};

const wait = async function(v) {
    let ret = await delayIt(v+1);
    ret += await delayIt(2 * ret);
    return ret;
};

const iter = {};

iter[Symbol.iterator] = function* () {
  for(let i=0; i<3; ++i) {
    yield wait(i);
  }
};

document.querySelector('div').innerHTML = '';

for(let x of iter) {
  x.then(d => doDiv(d));
}

function doDiv(ret) {
  let div = document.querySelector('#div');
  div.appendChild(document.createTextNode(ret));
  div.appendChild(document.createElement('br'));
};
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>After a couple of seconds you will see a numbers appear. There will be three in all.</p>
<div id="div"></div>
$footer
EOF;



            