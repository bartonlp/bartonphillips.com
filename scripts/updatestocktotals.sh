#!/bin/bash
// This is run by crontab at 17:00 Mon-Fri.

wget -qO- https://bartonphillips.com/stocks/stock-price-update.php?page=EndOfDay
