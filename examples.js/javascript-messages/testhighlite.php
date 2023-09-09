<?php
if($_POST) {
  echo <<<EOF
<pre class='line-numbers'>
<code class='language-php'>
&lt;?php
function test() {
  echo 'test';
  echo 'more test';
}
</code></pre>
EOF;
  exit();
}

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
  <script src="https://bartonphillips.net/js/jquery-3.6.3.js"></script>
  <link href="https://bartonphillips.net/css/prism.css" rel="stylesheet" />
  <style>
  html { font-size: 30px; }
  </style>
</head>
<body>
<pre class="line-numbers"><code class="language-css">
p { color: red }
#test p { color: red; border: 1px solid black; }
</code></pre>
<p>
  This is code <code class="language-css">p { color: red }</code>
</p>

<p id="dynamic"></p>
<p id="dynamic2"></p>

<script src="https://bartonphillips.net/js/prism.js"></script>
<script>
  $("#dynamic").html(`This is some inline text
  <pre class='line-numbers'>
  <code class='language-php'>
  &lt;?php function test() { echo 'test'  }
  </code></pre>`);
  
  $.ajax({
    url: "testhighlite.php",
    type: "post",
    data: "test",
    success: function(data) {
      console.log("data: ", data);
      $("#dynamic2").html(data);
      //const d = document.getElementById("dynamic2");
      //Prism.highlightElement(d);
      Prism.highlightAll();
    }
  });     
</script>
</body>
</html>
EOF;
