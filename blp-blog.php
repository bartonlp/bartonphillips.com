<?php
//   $Debug=1; // if enabled then show page as regualar viewer instead of ME
require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp;
$ref = $_SERVER['HTTP_REFERER'];

// Post Comment

if($id = $_POST['id']) {
  // id is either -1 if a general comment or a blogid if a reply to a specific item.
  
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

  if(!$title || !$comment) {
    echo "<h1>Error No content</h1>";
    exit();
  }
  
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

// Leave a general comment

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
<p class="center"><span class="red">No HTML allowed.</span>
HTML markup will be escaped so if you write '&lt;p&gt;Test&lt;/p&gt;' it will be turned into
'&amp;lt;p&amp;gt;Test&amp;lt;/p&amp;gt;'. Sorry.</p>
<form action="$S->self" method="post">
<table>
<tr><th>Title</th><td><input name="title" type="text"/></td></tr>
<tr><th>Comment</th><td><textarea name="comment"></textarea></td></tr>
</table>
<p><span id="question">What is 4+5?
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
$h->css =<<<EOF
  <!-- local css -->
  <style>
main table, main table th, main table td {
  border: 1px solid black;
}
#blog {
  width: 97%;
}
#blog pre {
  margin-left: 2em;
}
#comments {
  border: 1px solid black;
}
#comments td {
  padding: 10px;
}
@media (max-width: 600px) {
  body {
    font-size: 1.2em;
  }
  #blog pre {
    font-size: 0.7em;
    margin-left: 0px;
  }
}
  </style>
EOF;

$b->statcounter = false;
$b->msg = "<hr>";

list($top, $footer) = $S->getPageTopBottom($h, $b);

$n = $S->query("select id, date, title, text from blog order by date desc");
$result = $S->getResult(); // because we do another query in the body of the while

if($n) {
  while(list($id, $date, $title, $text) = $S->fetchrow($result)) {
    $text = stripslashes($text);

    if($S->isBlp() && !$Debug) {
      $blp = "<th><a href='add-blog.php?page=edit&id=$id'>$id</a></th>\n";
    }
    // are there any comments?
    $comments = "";
    $nn = $S->query("select date, title, text from comments ".
                              "where blogid='$id' order by date desc");
    if($nn) {
      $comments = <<<EOF
<table id="reply">
<thead>
<tr><th>Date</th><th>Title</th><th>Comment</th></tr>
</thead>
<tbody>
EOF;
      
      while(list($blogdate, $blogtitle, $blogtext) = $S->fetchrow()) {
        $blogtext = preg_replace(array("/<script>/i", "~</script>~i"),
                                 array("&lt;script&gt;", "&lt;/script&gt;"), $blogtext);
        $blogtitle = preg_replace(array("/<script>/i", "~</script>~i"),
                                  array("&lt;script&gt;", "&lt;/script&gt;"), $blogtitle);

        $comments .= "<tr><td>$blogdate</td><td>$blogtitle</td><td>$blogtext</td></tr>\n";
      }
      $comments .= "</tbody>\n</table>\n";
    }

    $tbl .= <<<EOF
<tr>
$blp
<td><h3>Date: $date</h3>
<h2>$title</h2>
$text
<a href="$S->self?comment=$id">Reply</a>
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

  // General Comments
  
  $n = $S->query("select date, title, text from comments where blogid='-1' ".
                 "order by date desc");
  if($n) {
    $divcomments =<<<EOF
<table id="comments">
<caption>
General Comments
</caption>
EOF;
  
    while(list($date, $title, $text) = $S->fetchrow()) {
      $divcomments .= "<tr><td>$date: $title<br>$text</td></tr>";
    }
    $divcomments .= "</table><br>";
  }

   // Render main page
  
  echo <<<EOF
$top
<main>
<hr>
<h3>Leave me a <a href="$S->self?comment=-1">comment</a>
about our site or something you are interested in.</h3>
$divcomments
<table id="blog">
$tbl
</table>
$blp
</main>
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
