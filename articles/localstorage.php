<?php
// BLP 2018-03-31 -- convert localstorage.htm to a php file.

if($_GET['page'] == 'source') {
  $file = file_get_contents("localstorage.php");
  echo $file;
  exit();
}

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->title = "LocalStorage Example";
$h->desc = "LocalStorage Example. Resize a big image using JavaScript";
$h->keywords = "LocalStorage, Resize IMAGE with JavaScript";
$h->script =<<<EOF
<script src="https://bartonphillips.net/js/localstorage.js"></script>
EOF;
$h->link =<<<EOF
<link rel="stylesheet" href="https://bartonphillips.net/css/theme.css">
EOF;
$h->banner = "<h1 class='center'>LocalStorage Example: Resize a big image in JavaScript</h1>";
$h->css =<<<EOF
<style>
.syntaxhighlighter {
  overflow: initial !important;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
<hr>
<div id="warnings"></div>
<div class="cnt">IMAGE NOT LOADED YET</div>
<p id="size">Size of the picture</p>
<p>To remove the localStorage and start over <button id="reload">Click Here</button> and
   notice the difference in load time.</p>
<div>
<img id="image" src=""></div>
<p>The first time this page is viewed the image is loaded from the web. The image is 1.6 Meg and takes
   a while to load. We then resize the image via a &lt;canvas&gt; to a 320 pixel wide image. We get the
   image URI which is the image data encoded as a base64 string. The URI is saved in
   localStorage.</p>
<p>The next time the page is accessed via the same browser the image is loaded from localStorage.
   The base64 URI is 457,054 bytes versus 1,653,948 bytes for the original image.
   Even if the original image is cached by the browser the cache file is still
   1.6 Meg while the base64 URI is 458k.</p>
<h3>A More Detailed Explanation</h3>
<p>I used the JQuery library (framework) to make the JavaScript a little easier. I use the local
   storage 'length' property to tell me if this is the first time the page has been loaded:</p>
<pre style="overflow: none;">
if(localStorage.length) { ... } // already been here at least once
else { ... } // this is the very first time here
</pre>
<p>When the page is initally loaded localStorage.length is zero. I Create the image object and
   load the image:</p>
<pre>
var image = new Image;
image.src = "images/CIMG00020.JPG"; // 1.6 Meg file, width=3,264, height=2,448
</pre>
<p>This is pretty standard stuff. You can access localStorage as an object
   <code>localStorage.clickcount</code>
   as an array <code>localStorage['clickcount']</code>
   or via functions <code>localStorage.getItem('clickcount')</code>
   or <code>localStorage.setItem('clickcount', 1)</code>. Also localStorage can only hold
   strings.</p>
<p>We now have to wait until the image object is loaded before we can proceed to resize it.
The original image is 3,264 by 2,448 pixels and we want the resized image to be 500 pixels wide.</p>
<pre>
$(image).load(function() {
  localStorage.orgsize = this.width * this.height;
  var ratio = this.width / 320;
  this.width = this.width / ratio;
  this.height = this.height / ratio;
  localStorage.imgsize = this.width * this.height;
  var canvas = document.createElement("canvas");
  canvas.width = this.width;
  canvas.height = this.height;
  var ctx = canvas.getContext("2d");
  ctx.drawImage(this, 0, 0, this.width, this.height);
  // Some ancient browsers (like IE) have a small limit to the URI size.
  try {
    var dataUri = canvas.toDataURL();
    localStorage.base64size = dataUri.length;
    ... more stuff
  } catch(e) {
    ... error message
  }
  // Local Storage is only 5 Meg so we could get an error if the resized image is too big.
  try {
    localStorage.setItem('img', dataUri);
  } catch (e) {
    ... error message
  }
  ... possibly more stuff
}
</pre>
<p>The actual source of this page has some additional logic for errors and fetching the size of the
   image at various stages.</p>
<p>The first time the load is quite long and you will notice a pause while the image is loaded. I
   could have waited to display any of the page until the image logic was done but I wanted to make
   it easy to see the delay of loading a 1.6 Meg image that is only going to be 500 pixels wide on
   the page. If you had access to the server where the picture resides you could scale the image
   many ways, but without access the the image you can still resize the image in JavaScript.</p>
<p>On subsequent page loads we use the saved base64 URI instead of loading the image via a seperate
   internet get.</p>
<pre>
if(localStorage.length) {
  localStorage.clickcount = Number(localStorage.clickcount)+1; // add on to count

  // Get the image URI from local storage. This URI is much smaller than the original image.
      
  img = localStorage.getItem('img');
  $("#image").attr('src', img);
}
</pre>
<p>The load time is much faster as you can see.</p>
<p>I have tested this on Linux with Firefox and Chrome and it works just great. I imagine that there
   may be problems with various versions of Internet Explorer on MS-Windows as there always are. I
   would expect this to work OK on Apple Mac's running Safari but I don't have a Mac.</p>

<p><button id="source">Show HTML Source</button></p>
<div id="showsource"></div>
<p><button id="jssource">Show JS Source</button></p>
<div id="showjs"></div>

<hr>
$footer
EOF;
