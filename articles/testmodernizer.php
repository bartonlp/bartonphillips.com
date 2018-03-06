<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($_POST['page'] == "post") {
  $features = $_POST['features'];
  $audio = $_POST['audio'];
  $video = $_POST['video'];
  
  $S->query("insert into browserfeatures (ip, agent, features, audio, video) ".
            "values('$S->ip', '$S->agent', '$features', '$audio', '$video') ".
            "on duplicate key update features='$features', audio='$audio', video='$video'");

  echo "OK";
  exit();
}

$h->extra = <<<EOF
  <script src="https://bartonphillips.net/js/featuretest.js"></script>

  <script>
jqXHR.always(function() {
  $('#supported').html(ok.join('<br>'));
  $('#notsupported').html(notok.join('<br>'));
});
  </script>

  <style type="text/css">
#blpimg {
   float: left;
   padding: 5px 10px;
}
td {
   vertical-align: text-top;
   padding: 5px;
}
article {
   text-align: center;
}
table {
   width: 80%;
   margin: 0 auto;
}
#browserid {
   color: blue;
}
pre.code {
   padding: 0.1em 0.5em 0.3em 0.7em;
   border-left: 11px solid #ccc;
   margin: 1.7em 0 1.7em 0.3em;
   overflow: auto;
   width: 93%;
}
  </style>
  
EOF;

$h->title = "Test Browser Features";
$h->banner = "<h1 class='center'>Test Your Browser's Features</h1>";

list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

echo <<<EOF
$top
<header>

<p>Your browser has identified itself as: 
<span id="browserid">{$S->agent}</span> via its &quot;User Agent String&quot;.</p>

<p>Using the browser's 'User Agent String' is not a great way to determin browser
features for several reasons. First and formost, the string can be forged very
easilly. Therefore it is much safer to detect if a browser supports features you wish
to use. For example, if you intend to use the browser storage feature 'localstorage'
you can test to see if it is supported before using it.</p>

<pre class="code">
if(window.localStorage) { ... } // supported
else { ... } // not supported
</pre>

<p>There is a package called Modernizr that does these feature detection tests for
you.</p>

<p>We use the Modernizr package to determin what features are support and which are
not.  See <a href="http://modernizr.com/">http://modernizr.com</a> for more
information on how 'modernizr' works.</p>

<p>Modernizr shows the following features for your browser:</p>

</header>
<article>
   <table border='1'>
    <thead>
      <tr><th>Supported</th><th>Not Supported</th></tr>
    </thead>
    <tbody>
      <tr><td id='supported'></td><td id='notsupported'></td></tr>
   </tbody>
   </table>
</article>
$footer
EOF;



