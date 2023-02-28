<?php

$date = date("Y-m-d H:i:s T");  
echo <<<EOF
<h1>Example using phpdate-js.js</h1>
<p>From https://github.com/andrasq/phpdate-js. This is in 'phpdate-js.js' in this directory.
It was designed for 'node' but I have removed the 'node' specific stuff so it now runs
in the browser.</p>
<p>'now' = $date. All times are 'now' except the first time.</p>
<div id='div1'></div>

<script src="phpdate-js.js"></script>
<script>
let str = `
2005-10-20 10:20:30 PST = \${gmdate("Y-m-d H:i:s T", '2005-10-20 10:20:30 PST')}<br>
\${gmdate("Y-m-d H:i:s T")}<br>
\${phpdate("c T")}<br>
\${gmdate("c T")}<br>
\${phpdate("r T")}<br>
\${phpdate("c Z")}<br>
\${phpdate("c T")}<br>
\${gmdate("c T")}<br>
\${phpdate("r T")}<br>
\${phpdate("c Z")}<br>
\${gmdate("r T")}`;

document.querySelector("#div1").innerHTML = str;
</script>
EOF;
