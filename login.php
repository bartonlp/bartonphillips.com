<html>
<body>
<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

if(isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  //$con = mysqli_connect('localhost','barton','bartol411','sample');
  //$result = mysqli_query($con, "SELECT * FROM `users` WHERE username='$username' AND
  //password='$password'");
  if(!$S->sql("SELECT * FROM sample.`users` WHERE username='$username' AND password='$password'")) {
    echo "Invalid user or password";
  } else {
    echo "<h1>Logged in</h1><p>A Secret for you...</p>";
  }
  //if(mysqli_num_rows($result) == 0)
    //echo 'Invalid username or password';
  //else
    //echo '<h1>Logged in</h1><p>A Secret for you....</p>';
} else {
?>
<form action="" method="post">
  Username: <input type="text" name="username"/><br />
  Password: <input type="password" name="password"/><br />
  <input type="submit" name="login" value="Login"/>
</form>
<?php
}
?>
</body>
</html>
