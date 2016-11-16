<?php
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

$ip = $S->ip;
$agent = $S->agent;
$site = $S->siteName;

$h->title = "Register";
$h->css = <<<EOF
  <style>
input {
  font-size: 1rem;
  padding-left: .5rem;
}
input[type="submit"] {
  border-radius: .5rem;
  background-color: green;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

if($_POST) {
  $name = $S->escape($_POST['name']);
  $email = $S->escape($_POST['email']);

  $sql = "insert into members (name, email, ip, agent, created, lasttime) ".
         "values('$name', '$email', '$ip', '$agent', now(), now()) ".
         "on duplicate key update email='$email', ip='$ip', agent='$agent', lasttime=now()";

  if(!$S->query($sql)) {
    echo "Error in Register POST<br>";
    throw(new Exception("register.php", $this));
  }
  $id = $S->getLastInsertId();
  
  if($S->setSiteCookie('SiteId', "$id", date('U') + 31536000, '/') === false) {
    echo "Can't set cookie in register.php<br>";
    throw(new Exception("Can't set cookie register.php " . __LINE__));
  }
  echo <<<EOF
$top
<h1>Registeration Posted</h1>
$footer
EOF;
  exit();
}

// Start Page

echo <<<EOF
$top
<h1>Register</h1>
<form method="post">
<p>Get our newsletter once a month. The newsletter has information on new and updated projects by me.
We currently have several projects:</p>

<ul>
<li>SiteClass: A mini framework for small sites.
  <a href="https://github.com/bartonlp/site-class">SiteClass on GitHub</a></li>
<li>SlideShow: A slideshow of images from a local or remote site.
  <a href="https://github.com/bartonlp/slideshow">Slideshow on GitHub</a></li>
<li>MySqlSlideshow: A slideshow of images via a MySql table.
  <a href="https://github.com/bartonlp/mysqlslideshow">MySqlSlideshow on GitHub</a></li>
<li>Blog Updates</li>
</ul>

<table>
<tbody>
<tr>
<td><input type="text" name="name" autofocus required placeholder="Enter Name"></td>
</tr>
<tr>
<td><input type="text" name="email" autofocus required placeholder="Enter Email Address"></td>
</tr>
</tbody>
</table>
<input type="submit" value="Submit">
</form>
$footer
EOF;
