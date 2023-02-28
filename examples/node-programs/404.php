<?php
header("HTTP/1.0 404 Not Found");
echo <<<EOF
<!DOCTYPE html>
<html>
<body>
<h1>Sorry, what you were looking for, we could not find</h1>
<h2>404 Not Found</h2>
<p>Goto my home page: <a href="https://www.bartonphillips.com">https://www.bartonphillips.com</a></p>
</body>
</html>
EOF;
