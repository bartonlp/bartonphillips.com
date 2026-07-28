<?php
// scraper-await-fetch.php
// Demo shows how to use PHPHtmlParser to scrape a webpage.
// The demo uses an 'async function' to get two set of information from my website.
// It uses a GET and a POST 'fetch' and awaits each and then returns the two results.
// The PHPHtmlParser\Dom is a great way to scrape information off of websites. I use it to
// scrape stock information from the Wall Street Journal website and it works great.
// https://github.com/paquettg/php-html-parser

// This demo uses my SiteClass mini-framework. Full documentation can be found at
// https://github.com/bartonlp/site-class

// Instanciate the SiteClass
/*
$html = file_get_contents("https://www.bartonphillips.com/index.php");

$dom = new DOMDocument();
@$dom->loadHTML($html);

$xpath = new DOMXPath($dom);

$others = $xpath->query("//*[@id='others']")->item(0);
$interesting = $xpath->query("//*[@id='interesting']")->item(0);

echo $dom->saveHTML($others);
echo $dom->saveHTML($interesting);
*/

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

// We have two demo server function that return pieces of my webpage.
// We use 'fetch()' instead of AJAX as it uses Promises and is easier to use.

// If $_GET['page'] equals 'one'. This is a GET call from 'sql()'

if($_GET['page'] == 'one') {
  $dom = new DOMDocument();

  $html = file_get_contents("https://www.bartonphillips.com/index.php");
  @$dom->loadHTML($html);

  $xpath = new DOMXPath($dom);

  $others = $xpath->query("//*[@id='others']")->item(0);
  
  $others = $dom->saveHTML($others);
    
  error_log("others: $others");
  echo $others;
  exit;
};

// If $_POST['page'] equals 'two'. This a POST call from 'sql()'

$input = file_get_contents("php://input");

$get = json_decode($input, true);

if($get['body'] == "page=two") {
  $dom = new DOMDocument();
  $html = file_get_contents("https://www.bartonphillips.com/index.php");
  @$dom->loadHTML($html);

  $xpath = new DOMXPath($dom);

  $interesting = $xpath->query("//*[@id='interesting']")->item(0);
  $interesting = $dom->saveHTML($interesting);
  error_log("interesting: $interesting");
  echo $interesting;
  exit();
}

// This is the source code and we change all of the < and > to '&amp;lt;', '&amp;gt;'

$sourceCode = escapeltgt(file_get_contents('scraper-await-fetch.php'));

$S->title = "Scraper Demo";
$S->banner = "<h1>Scraper, Async, Fetch</h1>";

// The css is for the two sections scraped from my webpage.

$S->css = <<<EOF
#r1, #r2 {
  border: 1px solid black;
  padding: .5rem;
  overflow: auto;
}
#info p {
  color: red;
}
pre {
  background: lightblue;
  margin: 5px;
  padding: 5px;
  overflow-x: auto;
}
.mylinks {
  text-align: center;
  margin: auto;
  border-spacing: .5rem;
}
EOF;

$S->h_script = <<<EOF
<!-- We use syntaxhighliter and the theme.css to show the sourcecode. -->
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">
<script>
jQuery(document).ready(function($) {
  // If you click on the 'Show Source Code' button we display the code via 'syntaxhightliter'.
  // We need to escape the dollar signs in the `...` sections with '\$' because this is part of the PHP.

  // Note, that 'syntaxhighlighter' changes the <pre class='brush: php'> into <div>s.
  // The div class is 'syntaxhightlighter'.

  $('#source').hide(); // Hide the source code.

  $('#showsource').on("click", function(e) {
     $('#showsource').remove();
     $('#source').show();
  });

  // While loading the async stuff put up a message

  $("#info").html("<p>Loading&emsp;<img src='https://bartonphillips.net/images/loading.gif'></p>");

  // Get the two 'fetch()' items from the server.

  sql().then(d => {
    // d has the two items r1 and r2.

    console.log("d:", d);

    let r1 = d.r1; //.others; //mysite;
    //r1 = r1.replaceAll(/</g, "&lt;").replaceAll(/>/g, "&gt;").replaceAll(/&gt; /g, "&gt;&#10;");

    console.log("r1:", r1);
    
    $("#r1").html(r1);
    
    let r2 = d.r2; //.interesting;
    //r2 = r2.replaceAll(/</g, "&lt;").replaceAll(/>/g, "&gt;").replaceAll(/&gt; /g, "&gt;&#10;");

    console.log("r2:", r2);

    $("#r2").html(r2);
    
    $("#info").html(
`r1:
<div id='r1'>
\${r1}</div>
r2:
<div id="r2">
\${r2}</div>`);
  })
  .catch(err => {
    console.log("ERROR: " +err); // catch any errors
  });
  // an 'async function' that does a GET 'fetch()' and a POST 'fetch()'

  async function sql() {
    let r1 = await fetch("scraper-await-fetch.php?page=one").then(data => data.text()); //json());

    const payload = JSON.stringify({
      body: "page=two"
    });
    
    let r2 = await fetch("scraper-await-fetch.php", {
      method: 'POST',
      headers: {
      //'content-type': 'application/x-www-form-urlencoded'
      'content-type': 'application/json'
    },
      body: payload
    }).then(data => data.text()); //json());

    return {r1: r1, r2: r2};
  };
});
</script>
EOF;

[$top, $footer] = $S->getPageTopBottom();

// Now render the page.
// Note that the <pre> is turned into <div>'s by 'syntaxhightlighter' so we need to use the new
// class name as mentioned above.

echo <<<EOF
$top
<hr>
<p>This program gets two pieces of information from my home page (https://www.bartonphillips.com/index.php).</p>
<p>The 'r1' looks like this:</p>
<pre id='r1'>
</pre>
<p>The 'r2' looks like this:</p>
<pre id='r2'>
</pre>
<p>The information is displayed below in boxes 'r1' and 'r2'. You can look at the source code by clicking on
the button below.</p>

<button id="showsource">Show Source Code</button>
<div id="source">
<pre class='brush: php'>$sourceCode</pre>
</div>
<div id='info'></div>
<hr>
$footer
EOF;
