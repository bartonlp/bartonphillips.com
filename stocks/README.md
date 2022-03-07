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
* stock-mov-avg.php -- gets the diff since 2022-02-10 and current.

The following are helper program not mentioned on https://www.bartonphillips.com:

* stock.getalpha.php -- this does a alpha and iex request for a single stock.
* stockvalue.php -- get info from eix using stocks from the stocks table.

## Programs in https://www.bartonphillips.com/stocks

1. **stock-price-update.php**. This is the main program that uses **stock-price-update.js** and
**stock-price-update-worker.js**. It displays my stocks every five minutes during the working
hours of the stock markets during the week. 

1. **stock-price-update.js** is just the JavaScript
for the main program.

1. **stockaddedit.php** adds and updates the *stocks.stocks* table.

1. **stockanal.php** uses the *stocks.pricedata* table to do moving averages on my stocks in the
*stocks.stocks* table.

1. **stockdiv.php** uses data from *IEX* to compute and display my dividnet information.

1. **stockquotes.php** this is like **stock-price-update.php** but runs only once.

1. **stockvalue.php** get the price*qty values from the `values` table.

1. **stock-mov-avg.php** get the diff from 2022-02-10 to today and shows moving average high and low.

Last Modified: BLP 2022-03-07 -- 

## Contact me: [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
