<?php
// Client for node server.js

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$S->title = "PHP Client for Node Server";
$S->banner = "<h1>$S->title</h1>";
$S->preheadcomment =<<<EOF
<!--
This is the Client side of the node server.js.
To run this you must be on the server (bartonlp.com) in the the
/var/www/bartonphillips.com/examples/node-programs directory. Then you should run 'node server.js'.
It will output 'server is running on port 3000'. As you interact with this client you will see
'console.log' messages under the initial message. NOTE: you will have to change the https.createServer() section
to use your certificates.
You can run the client-for-node-server.php from any browser on any computer anywhere.
-->
EOF;
$S->b_inlineScript =<<<EOF
$("input[type='text']").on("keydown", function(e) {
  if(e.keyCode == 13) {
    $("input[type='submit']").trigger("click");
  }
});

$("input[type='submit']").on("click", function() {
  let name = $("input[name='name']").val();
  let test = $("input[name='test']").val();
  console.log(`values: ${name}, \${test}`);
  $.ajax({
    url: "https://bartonphillips.com:3000/getit",
    method: "POST",
    data: {name: name, test: test},
    success: function(data) {
      console.log(`data: \${data}`);
      $("#results").html(`<h2>\${data}</h2>`);
    },
    error: function(err) {
      console.log("Error: ", err);
      $("#results").html("Error No Server");
    }
  });
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>This is a PHP program. It does not run via the <b>server.js</b></p>
<hr>
<h2>This program requires that the <i>'node'</i> server be running on port 3000</h2>
<p>You must run <b>server.js</b> on your <i>Server</i> machine from the command line: <b>node server.js</b>.<br>
If you do not have the server running you will get browser error message <b>This site can't be reached</b> or something like that.<br>
NOTE: you will have to change the https.createServer() section to use your certificates.</p>
<a href="https://bartonphillips.com:3000/hi/Something/Special">/Something/Special</a> This is <i>/hi/Something/Special</i>.<br>
<a href="https://bartonphillips.com:3000/hi/29">29</a> This is <i>/hi/29</i><br>
You can enter either of these at the location bar.<br>
<a href="https://bartonphillips.com:3000?name=Big Test&test=How big">Via a query string</a>
This would be <i>/?name=Big Test&test=How big</i>. You could enter <i>/?name=Looks&test= good</i> at the location bar.<br>
The next group is an <i>Ajax</i> call.<br>
  <input type="text" name="name" value="Barton"><br>
  <input type="text" name="test" value="1"><br>
  <input type="submit" value="Submit">
  <div id="results"></div>
<hr>
$footer
EOF;

