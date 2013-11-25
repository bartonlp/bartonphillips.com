<?php
require_once("/home/bartonlp/bartonphillips.com/htdocs/blp.i.php");
$S = new Blp; // count page

// The files in this directory, except for index.php, are all example files that do not have the right path info or the correct
// dbclass.connectinfo.i.php file. The actual runnable demo is in this directories run directory.
$h->title ="MySql Slideshow Examples";
$h->banner = "<h1>Run Examples of the MySql Slideshow Class</h1>";

$top = $S->getPageTop($h);
$footer = $S->getFooter("<hr/>");

echo <<<EOF
$top
<hr/>
<ul>
   <li><a href="run/serverside.php">Server Side Example</a></li>
   <li><a href="run/browserside.html">Ajax Browser Side Example</a></li>
</ul>

<p>Download a zip file with the MySqlSlideshow class files and examples:
   <a href="/download.php?file=mysqlslideshow.zip&path=mysqlslideshow" alt="download">Download Zip File</a></p>

EOF;

$serverside = highlight_file("serverside.php", true);
$browserside = highlight_file("browserside.html", true);
$addimages = highlight_file("addimages.php", true);
$addimage = highlight_file("addupdateimage.php", true);
$mysqlslideshow = highlight_file("mysqlslideshow.php", true);
$mysqlslideshowclass = highlight_file("mysqlslideshow.class.php", true);
$dbclass = highlight_file("dbclass.i.php", true);

echo <<<EOF
<h2>Server Side Example: serverside.php</h2>
<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$serverside
</div>
<h2>Ajax Browser Side Example: browserside.html</h2>
<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$browserside
</div>
<h2>Add Images To Database Example: addimages.php</h2>
<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$addimages
</div>
<h2>Add Or Update An Image Example: addupdateimage.php</h2>
<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$addimage
</div>
<h2>mysqlslideshow.php</h2>
<p>This is used by the Ajax code to get various things by passing querys via GET.</p>
<ul>
<li>Return an image: mysqlslideshow.php?image=1&amp;type=raw<br>
Returns raw image for image with id=1.
Example <code> &lt;img src="mysqlslideshow.php?image=1&amp;type=raw" /&gt; </code>. This works for all browsers even IE</li>
<li>Return subject and description info: mysqlslideshow.php?info=1<br>
Returns information for image with id=1. The information looks like
&quot;<::subject::>subject text<::description::>description text" (see the Ajax example for how this is used)</li>
<li>Returns a list of id numbers for selected images: mysqlslideshow.php?ids=1&amp;where=id in(1,2,3)<br>
Returns the id for each of the images in the table. The &quot;where=&quot; is optional. &quot;where&quot;
is the conditional part of the mysql where clause. The returned list looks like &quot;1,2,3,4&quot; etc. This string can
be easily split into an array like this: <code> ids = data.split(',') </code>.</li>
<li>Returns a HTML table with all of the rows from the database table: mysqlslideshow.php?table=1</li>
</ul>
<p>Usage of this file can be found in the browserside.html file.</p>
<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$mysqlslideshow
</div>
<h2>mysqlslideshow.class.php</h2>
<p>This is the PHP class &quot;MySqlSlideshow&quot; which extends the Database class below.<br>
The class has the following methods:</p>
<ol>
<li>Class Constructor<br>
<code>public function __construct(\$host, \$user, \$password, \$database="mysqlslideshow", \$table="mysqlslideshow")</code></li>
<li>getImageIds<br>
<code>public function getImageIds(\$where="")</code></li>
<li>getImage<br>
<code>public function getImage(\$id, \$returntype="base64")</code></li>
<li>getInfo<br>
<code>public function getInfo(\$id)</code></li>
<li>imageQuery<br>
<code>public function imageQuery(\$where="")</code></li>
<li>returnResult<br>
<code>public function returnResult()</code></li>
<li>getNextImageRowData<br>
<code>public function getNextImageRowData()</code></li>
<li>getNumImages<br>
<code>public function getNumImages()</code></li>
<li>addImage<br>
<code>public function addImage(\$imagefile, \$subject="", \$desc="", \$type="link")</code></li>
<li>updateImageInfo<br>
<code>public function updateImageInfo(\$id, \$subject, \$desc)</code></li>
<li>displayAllImageInfo<br>
<code>public function displayAllImageInfo()</code></li>
</ol>
<p>Methods 1 through 4 and 11 are the primary methods and examples can be found in the serverside.php example file above.
Examples of methods 9 and 10 can be found in addimages.php and addupdateimage.php. Methods 5 through 8 are not shown in any
examples and are lower level methods.</p>

<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$mysqlslideshowclass
</div>
<h2>dbclass.i.php</h2>
<p>This is the Database class from which MySqlSlideshow is derived.</p>
<div style="border: 1px solid black; padding: 5px;height: 300px; overflow: auto;">
$dbclass
</div>
$footer
EOF;

?>