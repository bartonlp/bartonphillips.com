# stocks directory

These are my programs to give me information about my stocks.

This directory has the normal *symlinks*:

* aboutwebsite.php* -> ../../bartonlp/aboutwebsite.php
* beacon.php* -> ../../bartonlp/beacon.php
* robots.php* -> ../../bartonlp/robots.php
* sitemap.php* -> ../../bartonlp/sitemap.php
* tracker.php* -> ../../bartonlp/tracker.php

The stock file are:

* stock-price-update-worker.js -- a worker for stock-price-update.{php,js}
* stock-price-update.js -- javascript for stock-price-update.php
* stock-price-update.php -- the main program
* stockaddedit.php -- add/edit information in my stocks.stocks table.
* stockanal.php -- does moving averages on my stocks.
* stockdiv.php -- uses iex to get the dividend information
* stockquotes.php -- gets stock prives

The following are helper program not mentioned on https://www.bartonphillips.com:

* stockreaddata.php --  Reads the pricedata table.
* stock.getalpha.php -- this does a alpha and iex request for a single stock.
* stockfix.php -- a helper to fix stuff.
* stockinit.php -- a helper to init tables.
* stockinitsingle.php -- a helper to init a single stock
* stockquotealpha.php -- a helper that uses *alpha* to get divident info.

## Programs in https://www.bartonphillips.com

1. **stock-price-update.php**. This is the main program that uses **stock-price-update.js** and
**stock-price-update-worker.js**. It displays my stocks every five minutes during the working
hours of the stock markets during the week. 

   **stock-price-update.js** is just the JavaScript
for the main program.

   **stock-price-update-worker.js** is a *Worker* program that runs in a seperate thread and does 
all the hard work.
The *Worker* does a *query* of the *stocks.stocks* table (via a POST fetch) to get the stock names
and also scrapes the *Wall Street Journal* site for the *DJIA* (Dow Jones Average), change and
percent change.

1. **stockaddedit.php** adds and updates the *stocks.stocks* table.

1. **stockanal.php** uses the *stocks.pricedata* table to do moving averages on my stocks in the
*stocks.stocks* table.

1. **stockdiv.php** uses data from *IEX* to compute and display my dividnet information.

1. **stockquotes.php** this is like **stock-price-update.php** but runs only once.

## Contact me: [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
