<?php
// ssh2-sftp.php
// This uses PHP ssh2. We connect to 'bartonphillips.net' on port 2220 and then authenticate
// our password. Then we send this file to the 'analysis' directory on 'bartonphillips.net'.

echo "<h1>ssh2-sftp</h1>";
if(!($connection = ssh2_connect('bartonphillips.net', 2220))) {
  die("connect failed");
}
if(ssh2_auth_password($connection, 'barton', '7098653?') === false) {
  die("password failed");
}

// 'send' takes the connection, a LOCAL file, a remote file and the permissions for the creation of
// the file (like chmod 0644 newfile.txt). So './ssh2-sftp.php' is on the this computer, and
// newfile.txt is on 'bartonphillips.net' (which in this case is the same server but could be a
// different computer entierly).

if(ssh2_scp_send($connection,
                 './ssh2-sftp.php',
                 '/var/www/bartonphillipsnet/analysis/newfile.txt', 0644) === false) {
  die("send failed");
}
echo "<h2>DONE</h2><p>We were able to send ssh2-sftp.php to bartonphillipsnet/analysis</p>";

