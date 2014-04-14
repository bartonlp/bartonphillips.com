<?php
// BLP 2014-04-03 -- Oupput the /tmp/debugblp.txt file and optionally delete its contents.

if($_POST['submit']) {
  file_put_contents("/tmp/debugblp.txt", '');
  echo "<h1>File Emptied</h1>";
  exit();
}

$errors = file_get_contents("/tmp/debugblp.txt"); // Get the file's contents
// put a <hr> in front of groups each group starts [\d\d<month>...]...
$errors = preg_replace(array("~(\[\d{2})~","~\n~"), array("\n<hr>$1", "<br>"), $errors);
// display and offer to delete 
echo <<<EOF
<p>$errors</p>
<hr>
<form method="post">
<p style="color: red">
Delete File Contents <input type="submit" name="submit" value="Submit"/>
</p>
</form>
EOF;

?>