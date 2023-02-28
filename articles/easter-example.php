<?php
// BLP 2023-02-25 - use new approach
// This file show the date of easter given a year. It uses the class easterdatecalculator.php on
// github.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$day = new easterdatecalculator;

if (empty($_POST['year'])) {
  $year = date("Y");
} else {
  $year = $_POST['year'];
}

$easterExample = escapeltgt(file_get_contents("easter-example.php"));

$easterExample = <<<EOF
<p>Here is this file (easter-example.php). It uses 'SiteClass' (the require_once at the top)
which you can get from <a href="https://github.com/bartonlp/site-class">GitHub</a></p>
<div id='easterExample'>
<pre class='brush: php'>
$easterExample
</pre>
</div>
EOF;

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

$S->title = "Easter Date Calculator";
$S->extra = '<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>';

$S->h_inlineScript = <<<EOF
function re_calc() {
  document.forms['thisform'].submit();
}
jQuery(document).ready(function($) {
  $("#show").on("click", function() {
    $("#code").show();
    $(this).hide();
  });
});
EOF;

$S->link = '<link rel="stylesheet" href="https://bartonphillips.net/css/theme.css">';

$S->css = <<<EOF
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
#code { display: none; border: 1px solid black; }
#year {
  font-size: 1em;
  width: 4rem;
  padding: .5em;
}
EOF;

[$top, $footer] = $S->getPageTopBottom();

$path = __DIR__;

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

<p>This is done by a PHP Class. You can download the class from 
<a href="https://github.com/bartonlp/easterdatecalculator">GitHub</a>.</p>
<button id="show">Show Code</button>
<div id="code">$easterExample</div>
<form id='thisform' name='thisform' method='post'>
Enter The Year You Are Interested In:
<input type='text' id='year' name='year' value="$year"
  onchange='re_calc();' autofocus><br/>
Easter is on {$day->easter($year)}, day of year=$day->dayofyear<br/>
Mardi Gras on {$day->mardi_grass($year)}, day of year=$day->dayofyear<br>
Assention on {$day->assention($year)}, day of year=$day->dayofyear<br>
Pentacost on {$day->pentecost($year)}, day of year=$day->dayofyear<br>
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
