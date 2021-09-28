<?php
// I got this from https://github.com/ircmaxell off
// https://stackoverflow.com/questions/3531857/convert-var-dump-of-array-back-to-array-variable  
// This function takes a var_dump() image and creates a real variable.
// This will take the $test string which is the output of a var_dump()
// and convert it back to a variable $x. I then do a $y=print_r($x, true) and a
// ob_start(); var_dump($x); $z=ob_get_clean(); and then echo the output of these
// items along with the original string $test.

function unvar_dump($str) {
    if (strpos($str, "\n") === false) {
        //Add new lines:
        $regex = array(
            '#(\\[.*?\\]=>)#',
            '#(string\\(|int\\(|float\\(|array\\(|NULL|object\\(|})#',
        );
        $str = preg_replace($regex, "\n\\1", $str);
        $str = trim($str);
    }
    $regex = array(
        '#^\\040*NULL\\040*$#m',
        '#^\\s*array\\((.*?)\\)\\s*{\\s*$#m',
        '#^\\s*string\\((.*?)\\)\\s*(.*?)$#m',
        '#^\\s*int\\((.*?)\\)\\s*$#m',
        '#^\\s*bool\\(true\\)\\s*$#m',
        '#^\\s*bool\\(false\\)\\s*$#m',
        '#^\\s*float\\((.*?)\\)\\s*$#m',
        '#^\\s*\[(\\d+)\\]\\s*=>\\s*$#m',
        '#\\s*?\\r?\\n\\s*#m',
    );
    $replace = array(
        'N',
        'a:\\1:{',
        's:\\1:\\2',
        'i:\\1',
        'b:1',
        'b:0',
        'd:\\1',
        'i:\\1',
        ';'
    );
    $serialized = preg_replace($regex, $replace, $str);
    $func = create_function(
        '$match', 
        'return "s:".strlen($match[1]).":\\"".$match[1]."\\"";'
    );
    $serialized = preg_replace_callback(
        '#\\s*\\["(.*?)"\\]\\s*=>#', 
        $func,
        $serialized
    );
    $func = create_function(
        '$match', 
        'return "O:".strlen($match[1]).":\\"".$match[1]."\\":".$match[2].":{";'
    );
    $serialized = preg_replace_callback(
        '#object\\((.*?)\\).*?\\((\\d+)\\)\\s*{\\s*;#', 
        $func, 
        $serialized
    );
    $serialized = preg_replace(
        array('#};#', '#{;#'), 
        array('}', '{'), 
        $serialized
    );

    return unserialize($serialized);
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
    object(stdClass)#2 (2) {
      ["bar"]=>
      string(4) "bart"
      ["foo"]=>
      array(1) {
        [0]=>
        string(2) "re"
      }
    }
    [2]=>
    NULL
  }
}
EOF;

$x = unvar_dump($test);

$y = print_r($x, true) . "<br>";
ob_start();
var_dump($x);
$z = ob_get_clean();
eval('$Z='.var_export($test, true). ';');

echo <<<EOF
<pre>
print_r: $y
var_dump: $z
test:<br>$test
</pre>
EOF;
