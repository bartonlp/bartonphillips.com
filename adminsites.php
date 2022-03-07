<?php
// This is a list of adminstuff. These are the links to all of my administration stuff
// BLP 2021-03-26 -- added $GIT to GitStatus.

return <<<EOF
<!-- Admin text for all sights -->
<section id='adminstuff'>
<h2>Admin</h2>
<ul>
<li><a target="_blank" href="/webstats.php?blp=8653">Webstats</a></li>
<li><a target="_blank" href="/gitstatus.php">GitStatus$GIT[0]$GIT[1]</a></li>
<li><a target="_blank" href="/getcookie.php?blp=8653">Get/Reset Cookie</a></li>
<li><a target="_blank" href="https://newbernrotary.org/wp-admin/">New Bern Rotary ADMIN</a></li>
<li><a target="_blank" href="https://bnai-sholem.com/rjwebbuilder">Bnai-sholem ADMIN</a></li>
<li><a target="_blank" href="https://rivertownerentals.info/wp-admin">Rivertown Rental ADMIN</a></li>
<li><a target="_blank" href="/stocks/stock-price-update.php">Stock Quotes</a></li>
<li><a target="_blank" href="/stocks/mutualiex.php">Mutual Funds Quotes</a></li>
<li><a target="_blank" href="/stocks/stockaddedit.php">Add/Edit Stocks</a></li>
<li><a target="_blank" href="/stocks/stockdiv.php">Stock Dividends</a></li>
<li><a target="_blank" href="/stocks/stockvalue.php">Stock Value</a></li>
<li><a target="_blank" href="/stocks/stock-mov-avg.php">Stock Info</a></li>
<li><a target="_blank" href="/examples/">examples</a></li>
<li><a target="_blank" href="/examples.js/">examples.js</a></li>
<li><a target="_blank" href="/test_examples/">examples just for testing</a></li>
<li><a target="_blank" href="/memberstable.php">Bartonphillips members</a></li>
</ul>
</section>
EOF;

