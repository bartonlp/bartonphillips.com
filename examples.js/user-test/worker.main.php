<?php
// Demonstrates the use of a worker using AJAX calls (worker.ajax.php).
// This is the main program it uses worker.worker.js and worker.ajax.php
// See worker.ajax.php for a description of the 'test' table in the database 'test'.
// Load info from mysitemap.json for my framework SiteClass into $_site.
// Check SiteClass out at https://github.com/bartonlp/site-class.
// It has some documentation at that site.
// The worker.ajax.php uses the 'test' user and database while this program uses what is in
// mysitemap.json (which is usualy user=barton, database=barton).

//exit("<h1>Not Authorized</h1>");

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site); // $S gives access to my framework.

// escapeltgt() is a little utility that change < and > to &lt; &gt; from helper-functions.php.
// These three, $main, $worker, $ajax are displayed when id 'showfiles' is clicked.

$main = escapeltgt(file_get_contents("worker.main.php"));
$worker = escapeltgt(file_get_contents("worker.worker.js"));
$ajax = escapeltgt(file_get_contents("worker.ajax.php"));

$S->title = "Workers";
$S->banner = "<h1>Worker Demo</h1>";

$S->h_script =<<<EOF
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">

<script>
jQuery(document).ready(function($) {
  var w1 = new Worker("worker.worker.js");

  // Listen from messages frrom worker.worker.js
  
  w1.addEventListener("message", function(evt) {
    console.log("data: ", evt.data);
    if(Object.keys(evt.data)[0] == "ERROR" || Object.keys(evt.data)[0] == "DONE") {
      $("pre").html(Object.values(evt.data)[0]);
    } else {
      //let string = String.fromCharCode.apply(null, evt.data)
      let string = new TextDecoder("utf-8").decode(evt.data);
      console.log("Main string: ", string);
      $("pre").html(string);
    }
  });

  // now transfer array buffer

  const send = function(txt) {
    // use a map to create ascii to int.
    //bufView = Uint8Array.from(txt, x => x.charCodeAt());
    let bufView = new TextEncoder("utf-8").encode(txt);
    console.log("Main bufView: ", bufView);
    w1.postMessage(bufView, [bufView.buffer]);
  }

  $("#click").on("click", function() {
    var sql = $("input").val();
    send(sql);
    return false;
  });
  $("#clear").on("click", function() {
    $("pre").html("");
    return false;
  });
  $("#showfiles").on("click", function() {
    $("#files").show();
    $(this).hide();
    return false;
  });
});
</script>
EOF;
$S->css =<<<EOF
input {
  width: 100%;
  font-size: 1rem;
}
button {
  cursor: pointer;
  font-size: 1rem;
}
#files {
  display: none;
}
EOF;

// Use my framework to get the $top of the page which includes the <head> section
// the <body> tag and my banner which is in <header>.

[$top, $footer] = $S->getPageTopBottom();

// Render the page

echo <<<EOF
$top
<p>There is one table <b>test</b> that you can query.
That table has the following fields:</p>
<ul>
<li><i>id</i> which is an auto incrementing value.</li>
<li><i>name</i> which is an ascii field.</li>
<li><i>lasttime</i> which is an automatic time stamp.</li>
</ul>
<p>You can do things like: <i>select * from test</i>, or
<i>insert into test (name) value ('something')</i> or
<i>delete from test where id=10</i></p>
<p>You can add only 20 records and then the earliest records are deleted.</p>
<form>
Enter a SQL statement: <input type="text" autofocus ><br>
<button id="click">Click Me</button>
<button id="clear">Clear</button>
</form>
<pre>
</pre>
<hr>
<button id="showfiles">View the file
<b>worker.main.php</b>,<b>worker.worker.js</b> and <b>worker.ajax.php</b></button>
<div id="files">
<p>worker.main.php</p>
<pre class='brush: php'>
$main
</pre>
<p>worker.worker.js</p>
<pre class='brush: js'>
$worker
</pre>
<p>worker.ajax.php</p>
<pre class='brush: php'>
$ajax
</pre>
</div>
$footer
EOF;
