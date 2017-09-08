<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->title = "Complaints";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>Items For Chef's Meeting 8/29/2017</h1>
<ul>
<li>45 minute at breakfast last week. Gave up and went home and made an omelet. Why?
<li>Last week the pork chop was too tough to eat. Why? This is not the only time.
<li>When we have fish for dinner please serve it with tarter sauce.
<li>When there are finished plates, why do the staff return to the kitchen empty handed?
<li>Why can't you be consistent? One day the service and/or the food is quite good.
Then the next couple of days it is awful.
<li>Why are there no soup bowls for take out? And why no lids?
<li>Why are there no lids for ice cream take out?
<li>Can you cut up the onions a little more (appox. 1/4 inch long).
<li>Do you have a large gridel in the kitchen? Without one it will be much harder to get things cooked
in time.
<li>I think we are playing a game. We complain, you promis to fix things.
For a couple of day things look like they may be getting better.
Then things go back to the way they were.
</ul>

$footer
EOF;
