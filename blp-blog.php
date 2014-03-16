<?php
//   $Debug=1; // if enabled then show page as regualar viewer instead of ME

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp;
$ref = $_SERVER['HTTP_REFERER'];

// Post Comment

if($id = $_POST['id']) {
  if(!preg_match("~^http://www.bartonphillips.com~", $ref)) {
    echo "<h1>Ops, where did you come from?</h1><p>Ref: $ref</p>";
    exit();
  }

  if($_POST['sum'] != "9") {
    echo "<h1>Are you a robot or just poor at math?</h1>";
    exit();
  }
  $title = $_POST['title'];
  $comment = $_POST['comment'];

  $comment = escapeltgt($comment);
  $title = escapeltgt($title);
  
  if($_POST['bad']) {
    echo <<<EOF
<h1>Error</h1>
<p>You can not access this page directly, you must come here from our public pages. </p>
<p>You did not come here from our site!</p>
<p>Please visit <a href="blp-blog.php">our blog page</a> if you wish to leave a comment.</p>
EOF;
    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'];

    $message = "Attempt to leave a comment at blp-blog.\ntitle: $title\ncoment: $comment\nIP=$ip\nAgent=$agent\n";
    
    mail("bartonphillips@gmail.com", "blp-blog comments: Access Error", $message,
         "From: info@bartonphillips.com",
         "-f bartonphillips@gmail.com");

    exit();
  }
  
  $S->query("insert into comments (blogid, date, title, text) " .
            "values('$id', now(), '$title', '$comment')");
}

// Leave a comment

if(isset($_GET['comment'])) {
  $c = $_GET['comment'];
  // If the HTTP_REFERER is not US then display an error
  $bad = '';
  if(!preg_match("~^http://www.bartonphillips.com~", $ref)) {
    $bad = true;
  }

  $h->title = "Leave a comment";
  $h->banner = "<h1 class='center'>Leave a comment</h1>";
  list($top, $footer) = $S->getPageTopBottom($h, "<hr>");
  echo <<<EOF
$top

<p style="text-align: center"><span style="color: red">No HTML allowed.</span>
HTML markup will be escaped so if you write '&lt;p&gt;Test&lt;/p&gt;' it will be turned into
'&amp;lt;p&amp;gt;Test&amp;lt;/p&amp;gt;'. Sorry.</p>
<form action="$S->self" method="post">
<table style="width: 100%">
<tr><th style="width: 10%">Title</th><td><input style="width: 100%" name="title" type="text"\></td></tr>
<tr><th>Comment</th><td><textarea style="width: 100%; height: 300px" name="comment"></textarea></td></tr>
</table>
<p><span style="border: 1px solid black; background-color: lightblue; width: 30%;">What is 4+5?
<input name="sum" type="text"/></span></p>
<input type="submit"/>
<input type="hidden" name="id" value="$c"/>
<input type="hidden" name="bad" value="$bad"/>
</form>
$footer
EOF;
exit();      
}

// Main Page Start

$h->title = "Barton Phillips Blog";
$h->banner = "<h1 class='center'>Barton Phillips Blog</h1>";
$h->extra =<<<EOF
  <style>
#comments {
  border: 1px solid black;
}
#comments td {
  border: 1px solid black;
  padding: 10px;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

// Pickup blog articles
// blog looks like
/*
CREATE TABLE `blog` (
  `id` int(11) NOT NULL auto_increment,
  `date` date default NULL,
  `text` longtext,
  `lasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `title` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL auto_increment,
  `blogid` int(11) default NULL,
  `date` date default NULL,
  `title` text,
  `text` longtext,
  `lasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;  
*/

$n = $S->query("select id, date, title, text from blog order by date desc");
$result = $S->getResult(); // because we do another query in the body of the while

if($n) {
  while(list($id, $date, $title, $text) = $S->fetchrow($result)) {
    $text = stripslashes($text);
    if($S->isBlp() && !$Debug) {
      $blp = "<th style='width: 1px; padding: 5px'><a href='add-blog.php?page=edit&id=$id'>$id</a></th>\n";
    }
    // are there any comments?
    $comments = "";
    list($r, $nn) = $S->query("select date, title, text from comments where blogid='$id' order by date desc", true);
    if($nn) {
      $comments = <<<EOF
<table border="1" style="padding: 5px; width: 100%;">
<thead>
<tr><th>Date</th><th>Title</th><th>Comment</th></tr>
</thead>
<tbody>
EOF;
      while(list($blogdate, $blogtitle, $blogtext) = $S->fetchrow()) {
        $blogtext = preg_replace(array("/<script>/i", "~</script>~i"), array("&lt;script&gt;", "&lt;/script&gt;"), $blogtext);
        $blogtitle = preg_replace(array("/<script>/i", "~</script>~i"), array("&lt;script&gt;", "&lt;/script&gt;"), $blogtitle);
        $comments .= "<tr><td>$blogdate</td><td>$blogtitle</td><td>$blogtext</td></tr>\n";
      }
      $comments .= "</tbody>\n</table>\n";
    }

    $tbl .= <<<EOF
<tr>
$blp
<td style="padding: 5px"><h3>Date: $date</h3>
<h2>$title</h2>
<hr>
$text
<br><a href="$S->self?comment=$id">Reply</a>
$comments
</td>
</tr>
EOF;
  }

  if($S->isBlp() && !$Debug) {
    $blp = "<a href='add-blog.php'>Add a new blog entry</a><br>\n";
    if($n) {
      $blp .= "<p>To edit an Item click on the id number.</p>\n";
    }
  }

  list($r, $n) = $S->query("select date, title, text from comments where blogid='-1' order by date desc", true);
  if($n) {
    $divcomments =<<<EOF
<table id="comments" style="width: 100%;">
<caption style="font-size: 18pt; font-weight: bold;">
General Comments
</caption>
EOF;
  
    while(list($date, $title, $text) = $S->fetchrow()) {
      $divcomments .= "<tr><td>$date: $title<br>$text</td></tr>";
    }
    $divcomments .= "</table><br>";
  }

  echo <<<EOF
$top
<hr>
<h3>Leave me a <a href="$S->self?comment=-1">comment</a> about our site or something you are interested in.</h3>
$divcomments
<table border="1" style="width: 100%">
$tbl
</table>
$blp
$footer
EOF;
} else {
  echo <<<EOF
$top
<p>No blog items found</p>
$blp
$footer
EOF;
}
?>