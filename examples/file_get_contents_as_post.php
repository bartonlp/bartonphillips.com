<?php
// Use file_get_contents to do a POST
// By doing a post instead of a get we can 1) hide what is being sent and 2) pass much bigger
// payloads.

/*
CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `finger` varchar(50) NOT NULL,
  `count` int DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`email`,`finger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
*/

// This is the AJAX. It can be in this file or another file.
// Usually if we are doing database actions we will instaniate the Database class. If we need to
// output significan amounts of data we can use SiteClass.

$_site = require_once(getenv("SITELOADNAME")); // We would normally get the info from mysitemap.json first.

if($_POST['page'] == 'form') {
  $email = $_POST['email'];
  $name = $_POST['name'];

  $S = new Database($_site);

  if(!$S->sql("select finger, count, created, lasttime from members where name='$name' and email='$email' order by lasttime")) {
    echo "Not Found";
    exit();
  }
  
  while([$finger, $count, $created, $lasttime] = $S->fetchrow('num')) {
    $rows .= "<tr><td>$finger</td><td>$count</td><td>$created</td><td>$lasttime</td><tr>";
  }

  $tbl = <<<EOF
<table border=1>
<thead>
<tr><th>Finger</th><th>Count</th><th>Created</th><th>Last</th></tr>
</thead>
<tbody>
$rows
</tbody>
</table>
EOF;
  echo $tbl;
  exit();
}

// The rest of this could be in a seperate file.

$S = new $_site->className($_site); // $S gives access to my framework.

// We make an options array with application/x-www-form-urlencoded.
// The method is POST and the content is what we want to send to the AJAX function

$options = ['http' => [
  'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
  'method'  => 'POST',
  'content' => http_build_query(['page'=>'form', 'name'=>'Barton Phillips', 'email'=>'bartonphillips@gmail.com'])
  ]
];

// Now we create a context from the options

$context  = stream_context_create($options);

// Now this is going to do a POST!
// NOTE we must have the full url with https!
// If we are doing a post that does not need to return anything we can avoid the assignment.

$tbl = file_get_contents("https://www.bartonphillips.com/examples/file_get_contents_as_post.php", false, $context);

$S->title = "file_get_contents to do a Post";
$S->banner = "<h1>$S->title</h1>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<p>We have asked the database to retrieve the records for <b>Barton Phillips</b> with the email address <b>bartonphillips@gmail.com</b>
<p>Here are the results:</p>
$tbl
<hr>
$footer
EOF;
