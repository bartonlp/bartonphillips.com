<?php

function unserializeDump($str, &$i = 0) {
    $strtok = substr($str, $i);
    switch ($type = strtok($strtok, "(")) { // get type, before first parenthesis
         case "bool":
             return strtok(")") === "true"?(bool) $i += 10:!$i += 11;
         case "int":
             $int = (int)substr($str, $i + 4);
             $i += strlen($int) + 5;
             return $int;
         case "string":
             $i += 11 + ($len = (int)substr($str, $i + 7)) + strlen($len);
             return substr($str, $i - $len - 1, $len);
         case "float":
             return (float)($float = strtok(")")) + !$i += strlen($float) + 7;
         case "NULL":
             return NULL;
         case "array":
             $array = array();
             $len = (int)substr($str, $i + 6);
             $i = strpos($str, "\n", $i) - 1;
             for ($entries = 0; $entries < $len; $entries++) {
                 $i = strpos($str, "\n", $i);
                 $indent = -1 - (int)$i + $i = strpos($str, "[", $i);
                 // get key int/string
                 if ($str[$i + 1] == '"') {
                     // use longest possible sequence to avoid key and dump structure collisions
                     $key = substr($str, $i + 2, - 2 - $i + $i = strpos($str, "\"]=>\n  ", $i));
                 } else {
                     $key = (int)substr($str, $i + 1);
                     $i += strlen($key);
                 }
                 $i += $indent + 5; // jump line
                 $array[$key] = unserializeDump($str, $i);
             }
             $i = strpos($str, "}", $i) + 1;
             return $array;
         case "object":
             $reflection = new ReflectionClass(strtok(")"));
             $object = $reflection->newInstanceWithoutConstructor();
             $len = !strtok("(") + strtok(")");
             $i = strpos($str, "\n", $i) - 1;
             for ($entries = 0; $entries < $len; $entries++) {
                 $i = strpos($str, "\n", $i);
                 $indent = -1 - (int)$i + $i = strpos($str, "[", $i);
                 // use longest possible sequence to avoid key and dump structure collisions
                 $key = substr($str, $i + 2, - 2 - $i + $i = min(strpos($str, "\"]=>\n  ", $i)?:INF, strpos($str, "\":protected]=>\n  ", $i)?:INF, $priv = strpos($str, "\":\"", $i)?:INF));
                 if ($priv == $i) {
                     $ref = new ReflectionClass(substr($str, $i + 3, - 3 - $i + $i = strpos($str, "\":private]=>\n  ", $i)));
                     $i += $indent + 13; // jump line
                 } else {
                     $i += $indent + ($str[$i+1] == ":"?15:5); // jump line
                     $ref = $reflection;
                 }
                 $prop = $ref->getProperty($key);
                 $prop->setAccessible(true);
                 $prop->setValue($object, unserializeDump($str, $i));
             }
             $i = strpos($str, "}", $i) + 1;
             return $object;

    }
    throw new Exception("Type not recognized...: $type");
}

$test =<<<EOF
array(4) {
  ["foo"]=>
  string(8) "Foo"bar""
  [0]=>
  int(4)
  [5]=>
  float(43.2)
  ["af"]=>
  array(3) {
    [0]=>
    string(3) "123"
    [1]=>
    string(3) "bar"
    [2]=>
    string(4) "test"
  }
}
EOF;

$x = unserializeDump($test);
$y = print_r($x, true) . "<br>";
ob_start();
var_dump($x);
$z = ob_get_clean();

echo <<<EOF
<pre>
print_r: $y<br>
var_dump: $z<br>
test:<br>$test
</pre>
EOF;
