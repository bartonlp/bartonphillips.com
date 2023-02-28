<?php

if(empty($_GET['page'])) echo "<h1>Go Away</h1>";

$txt = file_get_contents($_GET['page']);
echo "RETURN: $txt";
