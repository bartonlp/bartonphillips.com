<?php
// BLP 2014-04-29 -- Do various git functions

if($cmd = $_POST['page']) {
  $site = $_POST['site'];
  chdir("/var/www/$site");
  $out = '';
  //error_log("cmd: $cmd, site: $site");
  exec("git $cmd", $out);
  $out = implode("\n", $out);
  $out = preg_replace(array("/</", "/>/"), array("&lt;","&gt;"), $out);
  echo <<<EOF
<hr>
<pre><b>$site</b>
$out
</pre>
EOF;
  exit();
}

$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  $("#git").click(function() {
    $("#results").html('');

    var page = 'status';
    $(['/applitec', '/bartonlp', '/bartonphillips', '/bartonphillipsnet', '/granbyrotary', '/messiah']).each(function(i, site) {
      console.log("page %s, site %s", page, site);
      $.ajax({
        url: "gitstatus.php",
        data: {page: page, site: site},
        type: 'post',
        success: function(data) {
               console.log(data);
               $('#results').append(data);
             },
             error: function(err) {
               console.log(err);
             }
      });
    });
  });
});
  </script>
EOF;
$h->css =<<<EOF
  <style>
#git {
  border-radius: .5rem;
  font-size: 1rem;
  margin-bottom: .5rem;
}
#results {
  width: 100%;
/*  height: 20rem; */
  overflow: auto;
/*  border: 1px solid black; */
}
  </style>
EOF;

$h->title = "GIT Status All";
$h->banner = "<h1>Show GIT Status All</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

$prefix = "/var/www";

$data = '';

$v = $prefix.$v;
$data = <<<EOF
<div>
<button id='git'>Status</button>
<div id='results'>
</div>
<hr>
</div>
EOF;

echo <<<EOF
$top
$data
$footer
EOF;
