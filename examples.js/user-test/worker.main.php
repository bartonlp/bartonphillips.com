<?php
// Demonstrates the use of a worker using AJAX calls.
// This is the main program for worker.main.php it uses
// worker.worker.js and worker.ajax.php
// See worker.ajax.php for a description of the 'test' table in the database 'test'.

// Load info from mysitemap.json for use by my framework SiteClass.
// Check SiteClass out at https://github.com/bartonlp/site-class.
// It has full documentation at that site.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site); // $S gives access to my framework.

// escapeltgt() is a little utility that change < and > to &lt; &gt;
$main = escapeltgt(file_get_contents("worker.main.php"));
$worker = escapeltgt(file_get_contents("worker.worker.js"));
$ajax = escapeltgt(file_get_contents("worker.ajax.php"));

$h->title = "Workers";
$h->banner = "<h1>Worker Demo</h1>";
$h->extra =<<<EOF
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">

<script>
jQuery(document).ready(function($) {
  var w1 = new Worker("worker.worker.js");

  w1.addEventListener("message", function(evt) {
    var string = String.fromCharCode.apply(null, evt.data)
    //var string = new TextDecoder("utf-8").decode(evt.data);
    console.log("Main string: ", string);
    $("pre").html(string);
  });

  // now transfer array buffer

  const send = function(txt) {
    // use a map to create ascii to int.
    bufView = Uint8Array.from(txt, x => x.charCodeAt());
    console.log("Main bufView: ", bufView);
    w1.postMessage(bufView, [bufView.buffer]);
  }

  $("#click").click(function() {
    var sql = $("input").val();
    send(sql);
    return false;
  });
  $("#clear").click(function() {
    $("pre").html("");
    return false;
  });
  $("#showfiles").click(function() {
    $("#files").show();
    $(this).hide();
    return false;
  });
});
</script>
EOF;
$h->css =<<<EOF
<style>
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
</style>
EOF;

// Use my framework to get the $top of the page which includes the <head> section
// the <body> tag and my banner which is in <header>.
list($top, $footer) = $S->getPageTopBottom($h);

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
