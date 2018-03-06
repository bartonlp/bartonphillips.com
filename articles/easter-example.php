<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if (empty($_POST['year'])) {
  $year = date("Y");
} else {
  $year = $_POST['year'];
}

$easterExample = escapeltgt(file_get_contents("easter-example.php"));

$easterExample = <<<EOF
<p>Here is this file (easter-example.php). It uses 'SiteClass' (the require_once at the top)
which you can get at <a href="https://github.com/bartonlp/site-class">here.</a></p>
<div id='easterExample'>
<pre class='brush: php'>
$easterExample
</pre>
</div>
EOF;

require_once("easterdatecalculator.php");
$day = new easterdatecalculator;

// My birthday is April 11, 1944. this shows how many times Easter has and will fall on that date.
// Of course I may not be around for all of these.

for($i=1944; $i < 2070; ++$i) {
  $days = easter_days($i);
  $t = 20 + $days;
  if($t > 30) {
    $t -= 30;
    $easter = "04-$t";
  } else {
    $easter = "03-$t";
  }
//  echo "$days, $t, $i-$easter<br>";

  if($easter == "04-11") {
    $mybday[] = "$i-$easter";
  }
}

$h->title = "Easter Date Calculator";
$h->extra = '<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>';

$h->script = <<<EOF
  <script>
function re_calc() {
  document.forms['thisform'].submit();
}
jQuery(document).ready(function($) {
});
  </script>
EOF;

$h->link = '<link rel="stylesheet" href="https://bartonphillips.net/css/theme.css">';

$h->css = <<<EOF
  <style>
.syntaxhighlighter {
  height: 10rem;
  font-size: .8rem !important;
}
code {
  background-color: lightgray;
  padding: .1rem .5rem;
}
#easterExample {
  width: 100%;
}
#year {
  font-size: 1em;
  width: 4rem;
  padding: .5em;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>When Is Easter?</h1>
<p>This page can calculate the date of Easter for many many years to come. The following holidays which are associated with
Easter can also be calculated:</p>
<ul>
<li>Fashing/Karnaval (Mardi Grass)   -49 days from Easter
<li>Ash Wednesday -46 days from Easter
<li>Good Friday    -2 days from Easter
<li>Assention     +39 days from Easter
<li>Pentecost     +49 days from Easter
</ul>

<p>This is done by a PHP Class. You can download the class
<a href="download.php?file=easterdatecalculator.php">here</a>.</p>
$easterExample
<form id='thisform' name='thisform' method='post'>
Enter The Year You Are Interested In:
<input type='text' id='year' name='year' value="$year"
  onchange='re_calc();' autofocus><br/>
Easter is on {$day->easter($year)} day of year=$day->dayofyear<br/>
Mardi Gras on {$day->mardi_grass($year)} day of year=$day->dayofyear<br>
Assention on {$day->assention($year)} day of year=$day->dayofyear<br>
Pentacost on {$day->pentecost($year)} day of year=$day->dayofyear<br>
</form>

<p>My birthday is April 11, 1944. Easter falls on that date on these years from 1944 to 2070</p>
<p>
EOF;

foreach($mybday as $date) {
  echo "$date<br>\n";
}
echo <<<EOF
<p>Of course I may not make it to 122 years old.</p>
<hr>
$footer
EOF;
