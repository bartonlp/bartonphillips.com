# stockquotes

Get stock quotes every 5 minutes from 'iex' and then send them to websocket clients.

* stock-price.html -- websocket client browser program
* stock-websocket-sync.js -- websocket server
* stock-websocket.js -- websocket server

The websocket server should be run as `node stock-websocket-sync.js` or the async version.

**stock-websocket-sync.js** uses *sync-mysql* and *sync-request* to do the *query* and *request*
of the *stocks* table and 'iex'.

**stock-websocket.js** user *promise-mysql2* and *request-promise* to do an async *query*
and *request*.

Because this is running as a seperate websocket server with *node* the delay due to the synchronous
*query* and *request* does not make any difference and it make the code MUCH simpler.

## Stock Info from IEX

```
[quote] => stdClass Object
  (
    [symbol] => BP
    [companyName] => BP p.l.c.
    [primaryExchange] => New York Stock Exchange
    [sector] => Energy
    [calculationPrice] => close
    [open] => 39.65
    [openTime] => 1518791506171
    [close] => 39.62
    [closeTime] => 1518814834937
    [high] => 40.02
    [low] => 39.51
    [latestPrice] => 39.62
    [latestSource] => Close
    [latestTime] => February 16, 2018
    [latestUpdate] => 1518814834937
    [latestVolume] => 4661403
    [iexRealtimePrice] => 
    [iexRealtimeSize] => 
    [iexLastUpdated] => 
    [delayedPrice] => 39.99
    [delayedPriceTime] => 1518817832033
    [previousClose] => 39.84
    [change] => -0.22
    [changePercent] => -0.00552
    [iexMarketPercent] => 
    [iexVolume] => 
    [avgTotalVolume] => 6823822
    [iexBidPrice] => 
    [iexBidSize] => 
    [iexAskPrice] => 
    [iexAskSize] => 
    [marketCap] => 131608767299
    [peRatio] => 21.07
    [week52High] => 44.615
    [week52Low] => 33.1
    [ytdChange] => -0.06512505899009
  )
)
```

# Contact me at [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
