<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp;

$errorhdr = <<<EOF
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta name="robots" content="noindex">
</head>
EOF;

if(!$S->isBlp()) {
  echo <<<EOF
$errorhdr
<body>
<h1>Sorry This Is Just For Designated Admin Members</h1>
</body>
</html>
EOF;

  exit();
}

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
  case 'POST':
    switch($_POST['page']) {
      case 'post':
        post($S);
        break;
      case 'postedit':
        postedit($S);
        break;
      default:
        throw(new Exception("POST invalid page: {$_POST['page']}"));
    }
    break;
  case 'GET':
    switch($_GET['page']) {
      case 'edit':
        start($S);
        break;
      default:
        start($S);
        break;
    }
    break;
  default:
    // Main page
    throw(new Exception("Not GET or POST: {$_SERVER['REQUEST_METHOD']}"));
    break;
}

function start($S) {
  $h->title = "Add to Blog";
  $h->banner = ($id = $_GET['id']) ? "<h1>Edit Blog Item</h1>" : "<h1>Add Blog Item</h1>";
  $h->extra = <<<EOF
  <script type="text/javascript" src="http://www.granbyrotary.org/js/date_input/jquery.date_input.js"></script>
  <link rel="stylesheet" href="http://www.granbyrotary.org/js/date_input/date_input.css" type="text/css">

  <script type='text/javascript'>
jQuery(document).ready(function($) {
  var auto = 1;

  $("#form")
  .after("<input type='button' id='render' style='display: none' value='Quick Preview'/>" +
        "<input type='button' id='autopreview' value='Stop Auto Preview' />");

  $("#form").after("<p>Quick Preview</p><div style='padding: 5px; border: 1px solid black' id='quickpreview'>");
  $("#quickpreview").html($("#form textarea").val());

  $("#autopreview").click(function() {
    if(auto) {
      $(this).val("Start Auto Preview");
      $("#render").show();
      auto = 0;
    } else {
      $(this).val("Stop Auto Preview");
      $("#render").hide();
      $("#render").click();
      auto = 1;
    }
  });

  $("#render").click(function() {
    $("#quickpreview").html($("#form textarea").val());
  });

  $("#form textarea").keyup(function() {
    if(!auto) return false;

    $("#quickpreview").html($("#form textarea").val());
  });

  $.extend(DateInput.DEFAULT_OPTS, {
    stringToDate: function(string) {
      var matches;
      if(matches = string.match(/^(\d{4,4})-(\d{2,2})-(\d{2,2})$/)) {
        return new Date(matches[1], matches[2] - 1, matches[3]);
      } else {
        return null;
      };
    },

    dateToString: function(date) {
      var month = (date.getMonth() + 1).toString();
      var dom = date.getDate().toString();
      if (month.length == 1) month = "0" + month;
      if (dom.length == 1) dom = "0" + dom;
      return date.getFullYear() + "-" + month + "-" + dom;
    }
  });
  $.date_input.initialize();
});  
  </script>
  <style type="text/css">
table {
  width: 100%;
}
input[type=text] {
  width: 99%;
}
textarea {
  width: 99%;
  height: 200px;
}
th {
  width: 20%;
}
  </style>
EOF;

  if($id) {
    $pageinfo = <<<EOF
<input type="hidden" name="page" value="postedit"/>
<input type="hidden" name="id" value="$id"/>
EOF;
    $n = $S->query("select date, title, text from blog where id='$id'");
    if(!$n) {
      $S->body = "<h2>No Record Found for ID=$id</h2>";
      paintit($S);
      exit();
    } else {
      list($date, $title, $text) = $S->fetchrow();
      $text = stripslashes($text);
      $text = preg_replace("/&/", "&amp;", $text);
   }
  } else {
    $pageinfo = '<input type="hidden" name="page" value="post"/>';
  }
  
  list($S->top, $S->footer) = $S->getPageTopBottom($h);

  $S->body = <<<EOF
<form id="form" action="$S->self" method="post">
<table border="1">
<tr><th style="width: 1px">Date</th>
<td style="width: 100%"><input type="text" class="date_input" name="date" value="$date"/></td>
</tr>
<tr><th style="width: 1px">Event&nbsp;Title</th>
<td><input type="text" name="title" value="$title"/></td>
</tr>
<tr>
<th style="width: 1px">Blog&nbsp;Text</th>
<td><textarea name="text">$text</textarea></td>
</tr>
</table>
$pageinfo
<input type="submit" value="submit"/>
</form>
<br><a href='blp-blog.php'>Goto Blog Page</a>
EOF;
  paintit($S);
}

function post($S) {
  // Varify that fields are pressent
  extract($_POST);

  $err = '';

  if(!$date) {
    $err .= "No Date<br>";
  }
  if(!$title) {
    $err .= "No Title<br>";
  }
  if(!$text) {
    $err .= "No Blog Text<br>";
  }

  if($err) {
    $h->title = "Missing field";
    $h->banner = "<h1>You are missing fields</h1>";
    $S->top = $S->getPageTop($h);
    $S->body = "$err\n<p>Please go back and try again</p>";
    paintit($S);
    exit();
  }
  $title = $S->escape($title);
  $text = $S->escape($text);

  $S->query("insert into blog (date, title, text) values('$date', '$title', '$text')");

  $h->title = "Blog Item Posted";
  $h->banner = "<h1>Blog Item Posted</h1>";
  $S->body = "Thank you your item is posted\n<a href='blp-blog.php'>Goto Blog Page</a>";
  list($S->top, $S->footer) = $S->getPageTopBottom($h);
  paintit($S);
}

function postedit($S) {
  extract($_POST);
  $h->title = "Added to Blog";
  $h->banner = "<h1>Blog Item Updated</h1>";

  list($S->top, $S->footer) = $S->getPageTopBottom($h);

  $title = $S->escape($title);
  $message = $S->escape($message);

  $S->query("update blog set date='$date', title='$title',text='$text' where id='$id'");

  $S->body = "<h2>Item updated</h2>\n<a href='blp-blog.php'>Goto Blog Page</a>";
  paintit($S);
}

function paintit($S) {
  echo <<<EOF
$S->top
$S->body
$S->footer
EOF;
}

?>
