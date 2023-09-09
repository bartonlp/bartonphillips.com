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
  <a href="./get/Something/Special">Something Different</a><br>
  <a href="./get">Less</a><br>

  <a href="server.php?name=Big Test&test=How big">Big</a><br>

  <input type="text" name="name" value="Barton"><br>
  <input type="text" name="test" value="1"><br>
  <input type="submit" value="Submit">
  <div id="results"></div>
<script>
$("input[type='submit']").on("click", function() {
  let name = $("input[name='name']").val();
  let test = $("input[name='test']").val();
  let one = "One";
  let two = "Two";
  $.ajax({
    url: `./gotoit/\${name}/\${test}`,
    //url: './gotoit',
    method: "POST",
    data: {name: name, test: test, one: one, two: two},
    success: function(data) {
      console.log(`data: \${data}`);
      $("#results").html(`<h2>\${data}</h2>`);
    },
    error: function(err) {
      console.log('Error: ', err);
    }
  });
});
</script>
<form action="server.php" method="post">
<input type="hidden" name="name" value="Hi">
<input type="hidden" name="test" value="There">
<button type="submit">Send Hi There to server.php</button>
</form>
</body>
</html>
EOF;
