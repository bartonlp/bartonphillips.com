<?php
// test out the ssh2 copy

echo "sftp<br>";
if(!($connection = ssh2_connect('bartonphillips.net', 2220))) die("connect failed");

if(ssh2_auth_password($connection, 'barton', '7098653?') === false) die("password failed");

if(ssh2_scp_send($connection, '/var/www/bartonlp/sftp.php', '/var/www/bartonphillipsnet/analysis/newfile.txt', 0644) === false)
  die("send failed");
echo "DONE";

