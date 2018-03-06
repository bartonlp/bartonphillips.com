<?php
// async-await-2.php
// Demo showing how to use an async function and await.
// Use my mini framework to make thing easier. Documentation is avaliable at:
// https://github.com/bartonlp/site-class

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site); // $S gives access to my framework.

// This is the target of the 'fetch' calls in the 'async function'

if($_POST['page'] == 'sql') {
  $sql = $_POST['sql'];
  $rows = [];

  // do a mysql query and then fetch the rows
  
  $S->query($sql);

  while($row = $S->fetchrow('assoc')) {
    $rows[] = $row;
  }

  // return the rows as json
  
  $rows = json_encode($rows);
  echo $rows;
  exit();
}

// Get the source file to display

$file = escapeltgt(file_get_contents("async-await-2.php"));

// This is the JavaScript

$h->script = <<<EOF
<!-- Get the syntaxhightlighter code and the theme.css -->
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">

<script>
// We are using jQuery. This is the 'ready' function which in the includes/header.i.php file.

$(function() {
  // The display file button

  $("button").on("click", function() {
    $("#fileinfo").css("display", "block");
  });

  // The async function that does the work. It takes an array of sql statements

  async function getSql([...sql]) {
    // set up the object for the 'fetch'. We do posts

    let opt = {
      body: "",
      method: 'post',
      headers: {
        'content-type': 'application/x-www-form-urlencoded'
      }
    };

    let url = 'async-await-2.php';
    let ret = [];

    for(let i=0; i < sql.length; ++i) {
      // do the fetch and parse the json
      opt.body = 'page=sql&sql=' + sql[i];

      // Here is what we do:
      // let tmp  = await fetch(url, opt);
      // tmp = tmp.json();
      // ret.push(tmp);

      ret.push((await fetch(url, opt)).json());
   }

    return ret; // an array
  };

  // call 'getSql' with three sql statements

  getSql(["select 45", "select curtime() as data", "select * from barton.tracker limit 3"])
  .then((data) => Promise.all(data))  // What we get is an array of promises
  .then(data => {
    // Display each in the div

    let disp = '';
    for(let i=0; i < data.length; ++i) {
      disp += display(data[i]);
    }

    $("#sql").html(disp);
  })
  .catch(err => console.log("ERR:", err)); // Catch any errors

  // Take appart the returned rows

  function display(data) {
    let disp = '';
    for(let v of data) {
      console.log("v:", v);
      for(let [k, vv] of Object.entries(v)) {
        disp += `\${k}: \${vv}<br>`;
      }
    }
    disp += "<br>";
    return disp;
  };
});
</script>
EOF;

$h->css = <<<EOF
<style>
#fileinfo {
  display: none;
}
button {
  font-size: 1rem;
  padding: .2rem;
  border-radius: .2rem;
  margin-bottom: 20px;
}
#sql {
  border: 1px solid black;
  padding: .5rem;
  display: table-cell;
}
</style>
EOF;

$h->banner = "<h1>async-await-2</h1>";

// Get the top and bottom part of the display.

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<button>Display the File</button>
<div id="fileinfo">
<p><b>async-await-2.php</b></p>
<pre class='brush: php'>
$file
</pre>
</div>
<p>This is the results from three <i>sql</i> calls via <i>fetch</i> in an <i>async function</i>
with <i>await</i>. The three <i>sql</i> statements are returned in an array. As a result
the first <i>then</i> needs to do a <i>Promise.all(data)</i> and send it to the second <i>then</i>.
You can review the code by clicking on the button above.</p>
<div id="sql"></div>
$footer
EOF;
