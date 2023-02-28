<?php
// BLP 2023-02-25 - use new approach
// Project info
// Links to my GitHub and PHPClasses Projects

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$S->css =<<<EOF
#octocat { /* GitHub Image */
  width: 80px;
  vertical-align: bottom;
}
EOF;
$S->banner = "<h1>My Projects</h1><hr>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<section id='projects'>
<h2>GitHub and PHPClasses Projects</h2>
 
<h3>PHP SiteClass Mini Framework</h3>
<p>This is a mini framework I have been using for almost 10 years. It has a database wrapper
and a number of methods that make my life a lot easier.</p>
<p>For example every web page needs a
&lt;head&gt; section and usually a &lt;footer&gt; as well as a &lt;header&gt;
(navigation and banner).
The framework makes these
things easy to live with.</p>
<p>The database wrapper lets you use several popular database engines
like 'mysql', 'mysqli', 'sqlite' and 'pod'. It is easy to use my framework with templeting
engines like Twig.</p>
<p>This framework is not &quot;All Things to All People&quot; like a number
of the well know frameworks try to be. This is a simple tool and therefore not nearly as
complex as some of the popular frameworks out there.</p>
<p>If you just have three or four virtual hosted sites and you need a quick way to get
everything working this is pretty easy.</p>
<p>The <b>SiteClass</b> framework is hosted at<br>
<a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://github.com/bartonlp/SiteClass">GitHub
<img id="octocat" src="https://bartonphillips.net/images/Octocat.jpg"></a>
and also at
<a target="_blank"
href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://www.phpclasses.org/package/9105-PHP-Create-database-driven-Web-sites.html">
<img src="https://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'></a>.
<br>Give it a try and let me know if you like it.</p>
<hr>

<h3>UpdateSite Class</h3>
<p>This class works with SiteClass. It lets you create sections or articles in a webpage that can be edited via the
web browser. The sections are stored in a database (MySql is prefered).</p>
<p>You can find my <b>UpdateSite Class</b> at<br>
<a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://github.com/bartonlp/updatesite">GitHub
<img id="octocat" src="https://bartonphillips.net/images/Octocat.jpg"></a>
and also at 
<a target="_blank"
href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&http://www.phpclasses.org/package/10042-PHP-Updateable-section-in-a-website-.html">
<img src="https://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'></a>
and the <a target="_blank" href="https://bartonlp.github.io/updatesite">Documentation</a>.</p>
<hr>

<h3>PHP Slide Show Class</h3>

<p>This class can be used to present a slide show of images.
It can extract lists of image files available on the local server or a remote Web server.</p>

<p>The image list is served to the browser which retrieves it with Javascript code that
performs AJAX requests to obtain the images to display.</p>

<p>For local server images the class returns a list of image file names.
For remote Web server, it retrieves a given remote page and parses it to return the
list of GIF, JPEG and PNG images linked from that page.</p>

<p>The Javascript libraries provided within this package control the
slide show presentation.</p>
  
<p>You can find my <b>Slide Show Class</b> at<br>
<a target="_blank"
href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://github.com/bartonlp/slideshow">GitHub
<img id="octocat" src="https://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank"
href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://www.phpclasses.org/browse/author/592640.html">
<img src="https://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
alt="php classes logo" /></a></p>
<hr>

<h3>PHP MySql Slide Show Class</h3>

<p>This package can be used to present a slide show from images listed in a database.
The main class can retrieve lists of images to be displayed from a MySQL database table.</p>

<p>The class can also add or update the slideshow image lists in the database table.
The actual images can be stored on the filesystem or in the MySql table as base64 data.</p>
  
<p>You can find my <b>MySql Slide Show Class</b> at<br>
<a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&http://github.com/bartonlp/mysqlslideshow">GitHub
<img id="octocat" src="https://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://www.phpclasses.org/browse/author/592640.html">
<img src="https://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>

<h3>RssFeed Class</h3>

<p>This package can read and get information from an RSS feed. It is simple to use.</p>
<p>You can find my <b>RssFeed Class</b> at<br>
<a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://github.com/bartonlp/rssfeed">GitHub
<img id="octocat" src="https://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://www.phpclasses.org/package/10074-PHP-Read-RSS-feeds.html">
<img src="https://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>
$footer
EOF;
