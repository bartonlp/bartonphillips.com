<?php
// scraper-await-fetch.php
// BLP 2021-09-26 -- remove hard r1, r2 and get it from bartonphillips.com/index.php
// Demo shows how to use PHPHtmlParser to scrape a webpage.
// The demo uses an 'async function' to get two set of information from my website.
// It uses a GET and a POST 'fetch' and awaits each and then returns the two results.
// The PHPHtmlParser\Dom is a great way to scrape information off of websites. I use it to
// scrape stock information from the Wall Street Journal website and it works great.
// https://github.com/paquettg/php-html-parser

// This demo uses my SiteClass mini-framework. Full documentation can be found at
// https://github.com/bartonlp/site-class

// Instanciate the SiteClass
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

use PHPHtmlParser\Dom;

// We have two demo server function that return pieces of my webpage.
// We use 'fetch()' instead of AJAX as it uses Promises and is easier to use.

// If $_GET['page'] equals 'one'. This is a GET call from 'query()'

if($_GET['page'] == 'one') {
  $dom = new Dom;
  // grab the file. We need the HTML not PHP so we use the 'https' protocal.
  // The returned information is just the html.
  
  $x = $dom->loadFromUrl("https://www.bartonphillips.com/index.php");

  // Get everything from the <section> with the id of 'others'
  $mysite = $x->find("#others");
  
  $ret = ['mysite'=>trim($mysite->innerHTML)]; //BLP 2021-09-26 -- remove leading and trailing spaces
  $ret = json_encode($ret);
  echo $ret;
  exit();
};

// If $_POST['page'] equals 'two'. This a POST call from 'query()'

if($_POST['page'] == 'two') {
  $dom = new Dom;
  // Same as above.
  $x = $dom->loadFromUrl("https://www.bartonphillips.com/index.php");
  // This time we get two <h2> item from each section.
  $interesting = $x->find("#interesting")->innerHTML;
  $ret = ['interesting'=>trim($interesting)]; // BLP 2021-09-26 -- remove leading and trailing spaces
  
  $ret = trim(json_encode($ret));
  echo $ret;
  exit();
}

// This is the source code and we change all of the < and > to '&amp;lt;', '&amp;gt;'
$sourceCode = escapeltgt(file_get_contents('scraper-await-fetch.php'));

// $h is an object that has information to include in the output.

$h->title = "Scraper Demo";
$h->banner = "<h1>Scraper, Async, Fetch</h1>";

// The css is for the two sections scraped from my webpage.

$h->css = <<<EOF
<style>
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
</style>
EOF;

$h->script = <<<EOF
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

  query().then(d => {
    // d has the two items r1 and r2.

    //console.log("d:", d);

    let r1 = d.r1.mysite;
    r1 = r1.replaceAll(/</g, "&lt;").replaceAll(/>/g, "&gt;").replaceAll(/&gt; /g, "&gt;&#10;");
//    let r1 = d.r1.mysite.replaceAll(/</g, "&lt;");
//    r1 = r1.replaceAll(/>/g, "&gt;");
//    r1 = r1.replaceAll(/&gt; /g, "&gt;&#10;");

    //console.log("r1:", r1);
    
    $("#r1").html(r1);
    
    let r2 = d.r2.interesting;
    r2 = r2.replaceAll(/</g, "&lt;").replaceAll(/>/g, "&gt;").replaceAll(/&gt; /g, "&gt;&#10;");

    //console.log("r2:", r2);

    $("#r2").html(r2);
    
    $("#info").html(
`r1:
<div id='r1'>
\${d.r1.mysite}</div>
r2:
<div id="r2">
\${d.r2.interesting}</div>`);
  })
  .catch(err => console.log(err)); // catch any errors

  // an 'async function' that does a GET 'fetch()' and a POST 'fetch()'

  async function query() {
    // The information comes back as 'json' so convert it.
    // This could have all been done with a single fetch but I have used two for demo purpuses.
    // The fist does a GET and the second does a POST.

    let r1 = await fetch("scraper-await-fetch.php?page=one").then(data => data.json());
    let r2 = await fetch("scraper-await-fetch.php", {
      body: "page=two", // make this look like form data
      method: 'POST',
      headers: {
        'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
      }
    }).then(data => data.json());

    return {r1: r1, r2: r2};
  };
});
</script>
EOF;

// Get the $top and $footer using the $h object above.

list($top, $footer) = $S->getPageTopBottom($h);

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
