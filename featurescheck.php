<?php
// Look at the bartonphillips.com browserfeatures table and send me an email on the daily
// activity.
require_once("/var/www/includes/siteautoload.class.php");

try {
  $S = new Blp();
  $current = date("Y-m-d");
  $query = "select agent, features, audio, video, lasttime, date(lasttime) as today ".
           "from browserfeatures group by agent,features, audio, video order by agent";

  $n = $S->query($query);
  $tblrows =<<<EOF
<table border="1" width="100%">
<thead>
<tr><th>Agent String</th><th>Not Supported</th><th>Audio</th><th>Video</th></tr>
</thead>
<tbody>
EOF;
  while($row = $S->fetchrow()) {
    extract($row);
    $features = preg_replace("/,/", ", ", $features);
    $audio = preg_replace("/,/", ", ", $audio);
    $video = preg_replace("/,/", ", ", $video);
   
    if($today < $current) {
      $td = "nottoday";
    } else {
      $td = 'today';
    }
    $tblrows .= "<tr class='$td'><td>$agent</td><td>$features</td><td>$audio</td><td>$video</td></tr>";
  }
  $tblrows .= "</tbody>\n</table>";
} catch(Exception $e) {
  echo "Error {$e->getMessage()}<br>";
}
$h->extra =<<<EOF
<script>
jQuery(document).ready(function($) {
  $(".nottoday").hide();
  var flip = 0;
  $("#full").click(function() {
    if(flip++ % 2 == 0) {
      $(".nottoday").show();
      $("#full").text("Today Only");
    } else {
      $(".nottoday").hide();
      $("#full").text("Full List");
    }
  });
});
</script>
EOF;
$h->banner = "<h1 style='text-align: center'>Browser Features</h1>";

list($top, $footer) = $S->getPageTopBottom($h);
echo <<<EOF
$top
<article>
<p>This is data gathered from our web sites: bartonphillips.com and granbyrotary.org. The information
shows the <i>User Agent String</i> the features that are <i>Not Supported</i> by the agent, and the
<i>Audio</i> and <i>Video</i> the agent claims to support.
The audio and video support are shown as <i>probably</i> or <i>maybe</i>.</p>
<p>By default today's accesses are shown. You can toggle between <i>Full List</i> and <i>Today Only</i></p>
<button id="full">Full List</button>
$tblrows
$footer
EOF;
