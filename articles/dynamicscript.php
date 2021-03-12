<?php
// create a data-url for <script> tag.
// dynamically create a <script> tag with a data url for the src= item
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);
        
$data =<<<EOF
<!DOCTYPE HTML>
<html>
<head>
<meta charset='utf8'/>
</head>
<body>
<h1>I am an IFRAME</h1>
<p>Dynamically created iframe using PHP.</p>
</body>
</html> 
EOF;

$data = "data:text/html;base64," . base64_encode($data);

/*
// this is with just straight javascript no jQuery
$thescript =<<<EOF
var cl = document.getElementById('clickme');
cl.onclick = function() {
  alert('click');
};
EOF;
*/

// This is the above with jQuery  
$thescript =<<<EOF
// by using 'on' this will work even though the paragraph is not yet appended.
$(document).on('click', '#clickme', function() {
  alert('click');
});
EOF;

// Turn the script into base64
$thescript = base64_encode($thescript);

// Here is the magic that makes a data url script
$script =<<<EOF
<script id="plugger">
(function(){
    var plug=document.createElement('script');
    plug.setAttribute("src", "data:application/x-javascript;base64,$thescript");
    plug.setAttribute("name", "dynamic script");
    document.getElementsByTagName("head")[0].appendChild(plug);
})();
$('#plugger').remove();
</script>
EOF;

$h->extra = <<<EOF
  <!-- Text highliting logic: http://alexgorbatchev.com/SyntaxHighlighter -->
  <script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
  <link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">

  <!-- dynamic script -->
$script
  <!-- regualar script -->
  <script id="script1">
jQuery(document).ready(function($) {
  // Do we support URL and Blob?
  var URL = window.URL || window.webkitURL,
      Blob = window.Blob || window.webkitBlob,
      blobOk;

  try {
    blobOk = !!new Blob();
  } catch (e) {
    blobOk = false;
  }
  if(!URL || !Blob || !blobOk) {
    var errdiv = document.createElement('div'), errmsg;
    errmsg = "<h1>WARNING</h1>"
    if(!URL) {
      errmsg += "window.URL not supported<br>";
    }
    if(!Blob || !blobOk) {
      errmsg += "window.Blob not supported<br>";
    }
    errmsg += "The JavaScript versions require URL and Blob, therefore they will not work.<hr>";
    $(errdiv).html(errmsg);
    $(errdiv).css({color: 'red', 'text-align': 'center', width: '100%'});
    $('article').before(errdiv);
    console.log(errmsg);
  } else {
    // Make a blob with the script text
    try {
    var blob = new Blob(["$(document).on('click', '#clickme2', function() {alert('click wow');});"],
                        {type: 'text/plain'}),
    // create a script element
    script = document.createElement('script'),
    // turn the blob into a url
    url = URL.createObjectURL(blob);
    // assign the url to the src of the script
    script.src = url;
    script.setAttribute("name", "javascript dynamic script");
    // add the script to the end of the head section
    document.head.appendChild(script);
    } catch(e) {
      alert("Error: "+e);
    }

    var blobtext = "\
<!DOCTYPE HTML>\
<html>\
<head>\
<meta charset='utf8'/>\
</head>\
<body>\
<h1>I am an IFRAME</h1>\
<p>Dynamically created iframe using JavaScript</p>\
</body>\
</html>", 
    blob = new Blob([blobtext], {'type': 'text/html'}),
    url = URL.createObjectURL(blob),
    section = document.getElementById('iframe'),
    iframe = document.createElement('iframe');
    iframe.src = url;
    iframe.width = '50%';
    section.appendChild(iframe);
    $('<p>Return to <a href="/">My Home Page</a></p>').appendTo(section);
  }
  $(".php").addClass("brush: js");
  $(".html").addClass("brush: xml");

  $('#script1').remove();
});
  </script>
  <!-- Default style if Highlighter is no working -->
  <style>
body > pre {
        padding: 0.1em 0.5em 0.3em 0.7em;
        border-left: 11px solid #ccc;
        margin: 1.7em 0 1.7em 0.3em;
        overflow: auto;
        width: 93%;
}

#blpimg {
        float: left;
        padding: 5px 10px;
}
  </style>
EOF;

$h->title = "Dynamically Load Scripts and Iframs";
$h->banner = "<h1 class='center'>Dynamic Load &lt;script&gt; and &lt;iframe&gt; via PHP and JavaScript</h1>";

list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

echo <<<EOF
$top
<article>
<p>There are many ways to load JavaScript, here are a few:</p>
<ul>
<li>&lt;script src='some-script-file.js'&gt;&lt;/script&gt;</li>
<li>Use a library function like jQuery's $.getScript(...)</li>
<li>Inject a script tag into the page using document.write(..)</li>
<li>Create a script element via document.createElement('script'), fill it in with a text node and
append it via document.head.appendChild(...)</li>
</ul>
<p>I am sure there are many more ways. I will discuss using PHP and a 'data-uri' and using JavaScript
a 'blob' and URL.createObjectURL(...). One possible advantage to this approch is that the
JavaScript is pretty obfuscated as it appears as a base64 string not easily readable code.</p>
<section>
<h2>Using PHP</h2>
<p>First with PHP. There are a lot of ways to do it with PHP but I am going to show you how to create
a script tag via a base64 'data-uri'.</p>

<pre class='brush: php'>
&lt;?php
// This uses jQuery but could all be done with straight JavaScript.
// Put some JavaScript code into the \$thescript variable.
// The JavaScript will catch a click
// on the element with id 'clickme' and fire an alert box.
\$thescript =&lt;&lt;&lt;EOF
// by using 'on' this will work even though the paragraph is not yet appended.
//$(document).on('click', '#clickme', function() {
//  alert('click');
//});
//EOF&#59;
</pre>

<p>
<p>Turn the script we created above into base64</p>

<pre class='brush: php'>\$thescript = base64_encode(\$thescript);</pre>

<p>Here is the magic that makes a data url script.<br>
We create this JavaScript and place it in the \$script variable.<br>
</p>
<pre class='brush: js'>
\$script = &lt;&lt;&lt;EOF

&lt;script id="plugger"&gt;
(function(){
    var plug=document.createElement('script');
    plug.setAttribute("src", "data:application/x-javascript;base64,\$thescript");
    plug.setAttribute("name", "dynamic script");
    document.getElementsByTagName("head")[0].appendChild(plug);
})();

$('#plugger').remove(); // Remove this script tag to keep it clean as everything has been done.
&lt;/script&gt;
<span>EOF;</span>
</pre>

<p>Now \$script has an anonymous JavaScript function. When we output the page we just need to include
this variable like so:</p>

<pre class='brush: html'>
echo &lt;&lt;&lt;EOF
&lt;!DOCTYPE HTML&gt;
&lt;html&gt;
&lt;head&gt;
&lt;meta charset='utf8'/&gt;
&lt;!-- jQuery --&gt;
&lt;script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"&gt;&lt;/script&gt;
&lt;!-- dynamic script --&gt;
\$script
&lt;/head&gt;
&lt;body&gt;
&lt;p id='clickme' style='color: red'&gt;ClickMe&lt;/p&gt;
&lt;!-- More stuff goes here --&gt;
&shy;EOF;
</pre>

<p>Now if you click the red 'ClickMe' paragraph the alert fires.</p>
<p id='clickme' style='color: red'>ClickMe</p>
<p>You can look at the code using Firebug or Chrome tools (and I am sure there is some way to
look at the code in IE -- maybe). You can also look at the code by typing CTRL-u on most browsers.
Note that the actual page code is a bit more complex than what I have shown above.</p>
</section>
<section>
<h2>Using JavaScript (No PHP needed)</h2>
<p>By using blobs and data-uri's you can create a dynamic script in JavaScript alone.</p>
<p>Again I am using jQuery. First we create a blob. You should check somehow to see if blobs
and data-uri's are
supported in the browser as some OLD (read that Internet Explorer) versions of browsers don't support
much HTML5 or for that matter much of anything.</p>
<p>The blob has pretty much the same code as used in the PHP example except we are using another
paragraph element with an id of 'clickme2'.</p>
<p>We then create a script element. We turn the blob into a data-uri and assign the uri to the 'src'
attribute of the script. We set the name attribute so it is easier to find the script in the code
if you look with the debugger. Then the script is appended to the 'head' section.</p>

<script type="syntaxhighlighter" class="brush: js"><![CDATA[
<script id="script1">
jQuery(document).ready(function($) {
  // Assign all three variables in one statement which is more efficient.
  // Make a blob with the script text
  var blob = new Blob(["$(document).on('click','#clickme2'), function() {alert('click wow');});"],
                      {type: 'text/plain'}),
  // create a script element
  script = document.createElement('script'),
  // turn the blob into a url
  url = URL.createObjectURL(blob);
  // assign the url to the src of the script
  script.src = url;
  script.setAttribute("name", "javascript dynamic script");
  // add the script to the end of the head section
  document.head.appendChild(script);
  $('#script1').remove();
});
<&shy;/script>
]]></script>
<p>Below is the green 'ClickMe2' paragraph:</p>
<p id='clickme2' style='color: green'>ClickMe2</p>
</section>
<section id='iframe'>
<h3>What Else?</h3>
<p>Iframes have always been a pain. Having to load the iframe from a site rather than being able to
dynamically create them was always a drawback. Now with data uri's you can dynamically create
the source for the iframe either with PHP on the server or via JavaScript on the client.</p>
<h3>Using PHP</h3>
<p>With PHP on the server all you have to do is convert the HTML you want to have in the iframe
to base64 and then add 'data:application/x-javascript;base64,' to the start.</p>

<pre class='brush: php'>
// Create the text
\$data =&lt;&lt;&lt;EOF
&lt;!DOCTYPE HTML&gt;
&lt;html&gt;
&lt;head&gt;
&lt;meta charset='utf8'/&gt;
&lt;/head&gt;
&lt;body&gt;
&lt;h1&gt;I am an IFRAME&lt;/h1&gt;
&lt;p&gt;Dynamically created iframe using PHP.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;
&shy;EOF;
// Turn it into a data uri
\$data = "data:text/html;base64," . base64_encode(\$data);

</pre>

<p>Then in your HTML source just add the iframe as usual with the 'src' tag.</p>
<script type="syntaxhighlighter" class="brush: xml"><![CDATA[
<iframe id="frame" style="width: 50%;" src="\$data"></iframe>
]]></script>

<p>That is all there is to it.</p>

<iframe id="frame" style="width: 50%;" src="$data"></iframe>

<h3>Using JavaScript</h3>
<p>Blobs and data-uri's are great fun. Another use for these is dynamically creating &lt;iframe&gt;
tags. It is nice to be able to dynamically create an iframe rather than having to load it from
a file somewhere. For a long time this was just not possible or very very hard to achieve. In the
past I have resorted to temporary files or trickie 'eval' code. This blob-data-uri way is much
nicer I think.</p>

<script type="syntaxhighlighter" class="brush: js"><![CDATA[
var URL = window.URL || window.webkitURL,
if(window.Blob && URL) {
  var blobtext = "\
<!DOCTYPE HTML>\
<html>\
<head>\
<meta charset='utf8'/>\
</head>\
<body>\
<h1>I am an IFRAME</h1>\
<p>Dynamically created iframe using JavaScript.</p>\
</body>\
</html>", 
  blob = new Blob([blobtext], {'type': 'text/html'}),
  url = URL.createObjectURL(blob),
  iframe = document.createElement('iframe');
  iframe.src = url;
  iframe.width = '50%';
  document.body.appendChild(iframe);
}
]]></script>

</section>
</article>
<hr>
$footer
EOF;
