<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp; // count page
$h->title = "Using your &quot;/etc/hosts&quot; file";
$h->banner = "<h1>Using your &quot;/etc/hosts&quot; file</h1>";
$top = $S->getPageTop($h);
$footer = $S->getFooter("<hr/>");
echo <<<EOF
$top
<hr/>
<p>Why can't I access my home-hosted website from my own computer? This is a common problem. You have a DNS name for your site,
   say it is <i>http://www.mysite.com</i> but when you enter this name in your browser on your local network you get your router's
   administration window. What's up?</p>
<p>Well many (most) home routers and DSL modems direct your outside IP address to your router as a fail-safe measure. This way you
   will always be able to get to your router's administration page. So how do you access your home based web server from your home
   browser? First you can always use <i>http://127.0.0.1</i> or <i>localhost</i>. But why does <i>localhost</i> work?</p>
<p>If you look at your /etc/hosts file (you are running Linux arn't you? If not you deserver all the pain) you will probably
   see:</p>
<code>127.0.0.1 localhost</code>
<p>Now if your add the line:</p>
<code>127.0.0.1 www.mysite.com</code>
<p>you can now access your website using your DNS name from your local browser. You can also have absolute links in your web pages
   like:</p>
<code>&lt;a href=&quot;http://www.mysite.com/mypage.html&quot;&gt;Link to mypage&lt;/a&gt;</code>
<p>and the link will get to
   your web server and not to your router's administration page.</p>
$footer
EOF;
?>


