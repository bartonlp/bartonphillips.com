<?php
// async-await.php
// This demonstrates the use of an 'async function' and await. It also has a 'generator' that
// uses [Symbol.iterator] to let us use 'for of'.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

$S->banner = "<h1>async-await-class.php</h1>";
$S->css =<<<EOF
#div {
  font-size: 1.5rem;
}
EOF;

$S->b_inlineScript =<<<EOF
class GetInfo {
  constructor(ar) {
    this.ar = ar;
    this.index = 0;
  };

  [Symbol.iterator]() {
    return {
      next: () => {
        if(this.index < this.ar.length) {
          return {value: this.wait(this.ar[this.index++]), done: false};
        } else {
          // If we would like to iterate over this again without forcing manual update of the index
          this.index = 0; 
          return {done: true};
        }
      }
    }
  };
  
  delayIt(x) {
    return new Promise(res => {
      setTimeout(() => {
        res(x);
      }, 10000);
    });
  };

  async wait(v) {
      let ret = await this.delayIt(v+1);
      ret += await this.delayIt(2 * ret);
      return ret;
  };
};

document.querySelector('div').innerHTML = '';

const getInfo = new GetInfo([0, 1, 2, 3]);

for(let x of getInfo) {
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



            