<?php
// Because all of the links are to bartonlp.org I can get the rpi index and just display it.
// REMEMBER you need a .eval not .php because the .php will have been already evaluated. The .eval
// can be a symlink.
// Also NOTE that if this where just file_get_contents("./somefile.php") the evaluation is not
// done. PHP comes with many built-in wrappers for various URL-style protocols for use with the
// filesystem functions such as fopen(), copy(), file_exists(), file_get_contents() and filesize().
// In addition to these wrappers, it is possible to register custom wrappers using the stream_wrapper_register() function.

$page = file_get_contents("http://bartonphillips.org:8080/index.eval");
return eval("?>". $page);

