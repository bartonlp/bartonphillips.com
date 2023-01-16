<?php
// This is the client
echo <<<EOF
<!DOCTYPE html>
<html>
<head>
  <TITLE>Client</TITLE>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.3.2.min.js"
  integrity="sha256-Ap4KLoCf1rXb52q+i3p0k2vjBsmownyBTE1EqlRiMwA=" crossorigin="anonymous"></script>
  <script>jQuery.migrateMute = false; jQuery.migrateTrace = false;</script>
</head>
<body>
  <a href="/get/Something/Special">Something Different</a><br>
  <a href="/get/">More</a><br>

  <a href="server.php?name=Big Test&test=How big">Big</a><br>

  <input type="text" name="name" value="Barton"><br>
  <input type="text" name="test" value="1"><br>
  <input type="submit" value="Submit">
  <div id="results"></div>
<script>
$("input[type='submit']").on("click", function() {
  let name = $("input[name='name']").val();
  let test = $("input[name='test']").val();
  console.log(`values: \${name}, \${test}`);
  $.ajax({
    url: "/examples/node-programs/gotoit/",
    method: "POST",
    data: {name: name, test: test},
    success: function(data) {
      console.log(`data: \${data}`);
      $("#results").html(`<h2>\${data}</h2>`);
    },
    error: function(err) {
      console.log(`Error: \${err}`);
    }
  });
});
</script>
</body>
</html>
EOF;
