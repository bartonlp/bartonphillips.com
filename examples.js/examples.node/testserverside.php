<?php
// BLP 2023-02-26 - use new approach

$start = hrtime(true);

$_site = require_once(getenv("SITELOADNAME"));
$t1 = hrtime(true);
$S = new SiteClass($_site);
$t2 = hrtime(true);
$S->banner = "<h1>Here We Go</h1>";

[$top, $footer] = $S->getPageTopBottom();
$t3 = hrtime(true);
$msg =  <<<EOF
$top
<script>
try {
  // Create the performance observer.
  const po = new PerformanceObserver((list) => {
    for (const entry of list.getEntries()) {
      // Logs all server timing data for this response
      console.log('Server Timing', entry.serverTiming);
    }
  });
  // Start listening for `navigation` entries to be dispatched.
  po.observe({type: 'navigation', buffered: true});
} catch (e) {
  // Do nothing if the browser doesn't support this API.
  console.log("ERROR: ", e);
}
</script>

<h1>Server Side</h1>
$footer
EOF;

$end = hrtime(true);

header("Server-Timing: t1;desc=t1;dur=" . ($t1 - $start) .
       ", t2;desc=t2;dur=" . ($t2-$start) . ", end;desc=end;dur=" . ($end-$start));
//header("Server-Timing: miss, db;dur=53, app;dur=47.2");
echo $msg;
