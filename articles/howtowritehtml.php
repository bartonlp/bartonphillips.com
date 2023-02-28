<?php
// BLP 2023-02-25 - use new approach
// How to write HTML

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->title = "How to write html";
$S->banner = "<h1 class='center'>How To Write HTML</h1><hr>";
$S->css = <<<EOF
textarea, input {
  font-size: 1rem;
}
.example {
  border: 1px solid red;
  padding: 5px;
  background-color: yellow;
  color: black;
}
code {
  font-size: .8rem;
  color: gray;
  background-color: hsla(360, 25%, 5%, 0.1);
  padding: .1rem .3rem;
}
EOF;

$S->b_inlineScript =<<<EOF
jQuery(document).ready(function($) {
  var auto = 1, text;

  $("#inputs").append("<input id='clearit' type='button' value='Clear Text Area' style='color: red; font-size: 20pt;'/>");

  $("#clearit").click(function() {
    $("#inputtextarea").val("");
    $("#preview").empty();
  });
  
  $("#preview").html($("#previewform textarea").val());

  $("#autopreview").click(function() {
    if(auto) {
      $(this).val("Start Auto Preview").css({color: 'green', 'font-size': '20pt'});
      $("#render").show();
      auto = 0;
    } else {
      $(this).val("Stop Auto Preview").css({color: 'red', 'font-size': '20pt'});
      $("#render").hide();
      $("#render").click();
      auto = 1;
    }
  });

  $("#render").click(function() {
    // Don't allow any <script> tags!
    text = $("#previewform textarea").val();
    text = text.replace(/<\/?script.*?>/ig, "&lt;script NOT ALLOWED&gt;");
    $("#preview").html(text);
  });

  $("#previewform textarea").keyup(function() {
    if(!auto) return false;

    // Don't allow any <script> tags!
    text = $("#previewform textarea").val();
    text = text.replace(/<\/?script.*?>/ig, "&lt;script NOT ALLOWED&gt;");
    $("#preview").html(text);
  });

  // Show Source Code
  $("#showsource").click(function() {
    if(this.flag) {
      $(this).html("Show source code of this file");
      $("#showresults").hide();
    } else {
      $(this).html("Hide source code");
      if($("#showresults").html()) {
         $("#showresults").show();
      } else {
        var html = $("html").html();
        html = html.replace(/</g, '&lt;');
        html = html.replace(/>/g, '&gt;');
        html = html.replace(/\\n/g, '<br>');
        html = "&lt;!DOCTYPE html&gt;<br>&lt;html&gt<br>" + html + "<br>&lt;/html&gt;<br>";
        $("#showresults").html(html).css({border: '1px solid black',
          padding: '.5rem',
          overflow: 'auto',
          height: '20rem',
          backgroundColor: 'hsla(1, 65%, 85%, .5)'});
      }
    }
    this.flag = !this.flag;
    return false;
  });
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<div id="top-of-page2">This is the top of Page</div>
<a name="top-of-page"></a>
<div id="main" style="background-color: white; padding: 5px;">
   <h2>Write simple HTML</h2>

   <p>This tutorial shows you how to write simple HTML. We will not explore all of the HTML tags just the most useful.</p>
   <p>HTML code will appear in gray <code>&lt;p&gt;This is some HTML code&lt;p&gt;</code>.
      The examples are inside a yellow box with a red boarder.</p>
   <div class="example">
      <p>This is an example</p>
   </div>

   <hr>
   <h2>Headers</h2>
   <p>So lets start. First you will need headers. A header is written like this:</p>

   <pre>
<code>&lt;h1&gt;Some text to be a header&lt;/h1&gt;</code></pre>

   <p>It looks like this: </p>
   <h1 class="example">Some text to be a header</h1>
   <p>You can have headers with number from 1 through 6; for example: &lt;h3&gt; or &lt;h5&gt;.</p>
   <pre>
<code>&lt;h3&gt;This is an &amp;lt;h3&amp;gt; header&lt;/h3&gt;</code>
<code>&lt;h5&gt;While this is an &amp;lt;h5&amp;gt; header&lt;/h5&gt;</code></pre>
   <div class="example">
      <h3>This is an &lt;h3&gt; header</h3>
      <h5>While this is an &lt;h5&gt; header</h5>
   </div>

   <p>For more information on headers go to <a href="http://www.w3schools.com/html/html_headings.asp">w3school.com HTML Headers</a>.</p>
   <hr>

   <h2>&lt;br/&gt;, &lt;hr/&gt; and &lt;img .../&gt; Tags</h2>
   
   <p>Most HTML tags have a start tag and an end tag. Start tags look like &lt;tag&gt; and end tags look like &lt;/tag&gt;. The
      end tag has a forward slash '/' before the tag name like &lt;/h3&gt; or &lt;/h5&gt; above.</p>

   <p>There are a few exception tags that need only a start tag. These tags look like &lt;tag /&gt;. The forward slash is
      include in the start tag after the tag name (the space is not necessary but allowed).
      The trailing '/' is not required in traditional
      HTML but is required with XHTML. You can either use it or not. For example the following line will break with or
      without the '/'.</p>

   <pre>
<code>&lt;p&gt;This is a text line&lt;br/&gt;with a 'br' with a trailing '/'&lt;br&gt;and this 'br' has no trailing slash.
If this were XHTML the 'br' without the trailing slash would be a mistake.&lt;/p&gt;</code></pre>

   <p>The paragraph looks like this:</p>

   <div class="example">
      <p>This is a text line<br>with a 'br' with a trailing '/'<br>
         and this 'br' has no trailing slash. If this were XHTML the 'br' without the trailing slash would be a mistake.
      </p>
   </div>

   <p>The following useful tags do not have an end tag:</p>

   <ul>
      <li>Break &lt;br/&gt;</li>
      <li>Seperator line &lt;hr/&gt;</li>
      <li>Image &lt;img .../&gt; the ... represent attriblutes which we will get into later</li>
   </ul>

   <p>There are more but the above are the most important for now.</p>
   <p>For more information on <a href="http://www.w3schools.com/tags/tag_br.asp">'br'</a>,
   <a href="http://www.w3schools.com/tags/tag_hr.asp">'hr'</a> and
   <a href="http://www.w3schools.com/tags/tag_img.asp">'img'</a> click the links.</p>
   <hr>

   <h2>Paragraphs</h2>
   
   <p>To indicate a paragraph you use the &lt;p&gt; tag like this:</p>
   
   <pre>
<code>&lt;p&gt;This is a paragraph. Paragraphs wrap around if they are longer than their containing
element.&lt;/p&gt;</code></pre>

   <p>Here is how that looks:</p>

   <p class="example">This is a paragraph. Paragraphs wrap around if they are longer than their containing element.</p>

   <p>Here is some HTML that illistrates what we have seen so far:</p>

   <pre>
<code>&lt;h1&gt;This Is A Page Title&lt;/h1&gt;
&lt;p&gt;This is a paragraph on our page. This can have long lines that can break
any where but will be rendered to fit the
width of the page. Extra spaces like this       will be removed as will line breaks.&lt;/p&gt;</code></pre>

   <p>This HTML will render like this:</p>

   <div class="example">
      <h1>This Is A Page Title</h1>
      <p>This is a paragraph on our page. This can have long lines that can break
         any where but will be rendered to fit the
         width of the page. Extra spaces like this       will be removed as will line breaks.
      </p>
   </div>
   <p>Notice that the line breaks and extra spaces are not rendered. To force a space you can use an entilty &amp;nbsp;. Also to
      have a less than &lt; or a greater than &gt; sign you would enter &amp;lt; or &amp;gt;. For example:</p>
   <pre>
<code>&lt;p&gt;This paragraph has three spaces here&amp;nbsp;&amp;nbsp;&amp;nbsp; and a less than &amp;lt;
and greater than &amp;gt; sign.
It also has some line
breaks that will be removed as well as these extra spaces   .&lt;/p&gt;</code></pre>
   <div class="example">
      <p>This paragraph has three spaces here&nbsp;&nbsp;&nbsp; and a less than &lt;
and greater than &gt; sign.
It also has some line
breaks that will be removed as well as these extra spaces   .</p>
   </div>
   <p>For more information on paragraphs go to
      <a href="http://www.w3schools.com/html/html_paragraphs.asp">w3school.com HTML Paragraphs</a>.</p>
   
   <hr>

   <h2>Lists</h2>
   
   <p>Lists are nice in pages. To create lists one uses code like this:</p>

   <pre>
<code>&lt;ul&gt;
  &lt;li&gt;This is a bullet list. This the first bullet&lt;/li&gt;
  &lt;li&gt;This is the second bullet&lt;/li&gt;
&lt;/ul&gt;</code></pre>

   <p>This list looks like this:</p>

   <div class="example">
      <ul>
         <li>This is a bullet list. This is the first bullet</li>
         <li>This is the second bullet</li>
      </ul>
   </div>

   <p>You can have numbered lists. The &lt;ul&gt; stands for 'unordered list'. An 'ordered list' tag is &lt;ol&gt;.
      Here is some code:</p>

   <pre>
<code>&lt;ol&gt;
  &lt;li&gt;This is a bullet list. This the first bullet&lt;/li&gt;
  &lt;li&gt;This is the second bullet&lt;/li&gt;
&lt;/ol&gt;</code></pre>

   <p>The only change is the 'ul' tag is changed to a 'ol' tag. This code looks like this:</p>
    
   <div class="example">
      <ol>
         <li>This is a bullet list. This is the first bullet</li>
         <li>This is the second bullet</li>
      </ol>
   </div>

   <p>For more information on 'lists' go to <a href="http://www.w3schools.com/html/html_lists.asp">w3school.com HTML
      Lists</a></p>

   <hr>
   <h2>Tables</h2>
   
   <p>Tables are also very popular in pages. There are several tags associated with tables.</p>
   <ul>
      <li>&lt;table&gt; the start of a table tag.</li>
      <li>&lt;tr&gt; the start of a table row.</li>
      <li>&lt;th&gt; the start of a column header.</li>
      <li>&lt;td&gt; the start of a column detail.</li>
   </ul>
   <p>Each of these tags have a coresponding end tag: &lt;/table&gt;, &lt;/tr&gt;, &lt;/th&gt;, and &lt;/td&gt;. Tables are formed
      by having a 'table' tag and then one or more 'tr' row tags. Each 'tr' row tag can have 'th' or 'td' column tags. Here is
      an example table:</p>
   <pre>
<code>&lt;table border=&quot;1&quot;&gt;
  &lt;tr&gt;&lt;th&gt;Column One&lt;/th&gt;&lt;th&gt;Column Two&lt;/th&gt;&lt;/tr&gt;
  &lt;tr&gt;&lt;td&gt;Date 1&lt;/td&gt;&lt;td&gt;Date 2&lt;/td&gt;&lt;/tr&gt;
  &lt;tr&gt;&lt;td&gt;Date 3&lt;/td&gt;&lt;td&gt;Data 4&lt;/td&gt;&lt;/tr&gt;
&lt;/table&gt;</code></pre>

   <p>This table looks like this:</p>
   <div class="example">
      <table border="1">
         <tr><th>Column One</th><th>Column Two</th></tr>
         <tr><td>Data 1</td><td>Data 2</td></tr>
         <tr><td>Data 3</td><td>Data 4</td></tr>
      </table>
   </div>
   <p>Did you notice the 'border="1"' in the 'table' tag? That is an attribute. Most tags can take attributes. In this case
      the 'border' attribute makes the table render with borders as seen above. If we removed the border attribute the table
      would look like this:</p>
   <div class="example">
      <table>
         <tr><th>Column One</th><th>Column Two</th></tr>
         <tr><td>Data 1</td><td>Data 2</td></tr>
         <tr><td>Data 3</td><td>Data 4</td></tr>
      </table>
   </div>
   <p>We could also make the borders bigger by saying 'border="5"' which would produce a border of five pixles width like
      this:</p>
   <div class="example">
      <table border="5">
         <tr><th>Column One</th><th>Column Two</th></tr>
         <tr><td>Data 1</td><td>Data 2</td></tr>
         <tr><td>Data 3</td><td>Data 4</td></tr>
      </table>
   </div>
   <p>For more information on tables go to
      <a href="http://www.w3schools.com/html/html_tables.asp">w3school.com HTML Tables</a>.</p>
   <hr>

   <h2>Images</h2>
   <p>We showed you the 'img' tag above but here are some examples. The 'img' tag needs some attributes to be useful. The 'src'
      attribute lets you give the address of the image file you want to display.</p>
   <pre>
<code>&lt;img src=&quot;/images/msfree.png&quot; /&gt;</code></pre>
   <div class="example">
      <img src="https://bartonphillips.net/images/msfree.png" alt="MS Free image" >
   </div>
   <p>There are several other attributes for the 'img' tag that are useful:</p>
      <ul>
         <li>'width'</li>
         <li>'height'</li>
         <li>'alt'</li>
         <li>'border'</li>
      </ul>
   <p>The 'width' and 'height' attributes control the size of the image. The 'alt' provides a textual description of the image
      which is useful when a site is accessed by a brower that does not render images, for example a braill reader used by a blind
      visitor. The 'border' attribute is used the same way as with the 'table' tag.</p>
   <pre>
<code>&lt;img src=&quot;/images/msfree.png&quot; width=&quot;200&quot; height=&quot;200&quot; border=&quot;5&quot; alt=&quot;MS Free&quot; /&gt;</code></pre>

   <div class="example">
      <img src="https://bartonphillips.net/images/msfree.png" width="200" height="200" border="5" alt="MS Free" >
   </div>
   <p>As you can see the image is distorted. The actual image size is 100 by 31 pixles which is rectangular. By setting the width
      and height to 200 pixles each we have forced the image to be square. To keep the original ratios you can specify only the
      width or the height then the unspecified attribute will be scaled to maintain the same ratio.</p>
   <pre>
<code>&lt;img src=&quot;https://bartonphillips.net/images/msfree.png&quot; width=&quot;200&quot; border=&quot;5&quot; alt=&quot;MS Free&quot; /&gt;</code></pre>
   <div class="example">
      <img src="https://bartonphillips.net/images/msfree.png" width="200" border="5" alt="MS Free" >
   </div>
   <p>You will notice that the image becomes 'pixalized' as the size is expanded beyond the original dimentions.</p>
   
   <p>For a list of all attributes for the 'img' tag go to <a href="http://www.w3schools.com/tags/tag_img.asp">w3school.com img
      tag.</a><br>
      For further information about Images go to <a href="http://www.w3schools.com/html/html_images.asp">w3school.com HTML Images</a>.</p>

   <hr>
   <h2>Hyperlinks, Anchors and Links</h2>
   <p>The final tag we will discuss is the 'anchor' tag: &lt;a ... &gt;. This tag also needs attributes to be useful, and it takes
      an end tag &lt;/a&gt;. In web terms, a hyperlink is a reference (an address) to a resource on the web.
      Hyperlinks can point to any resource on the web: an HTML page, an image, a sound file, a movie, etc.
      An anchor is a term used to define a hyperlink destination inside a document.
      The HTML anchor element &lt;a&gt;, is used to define both hyperlinks and anchors.
      We will use the term HTML link when the &lt;a&gt element points to a resource,
      and the term HTML anchor when the &lt;a&gt; elements defines an address inside a document.</p>
   <p>To define a link you use the relative or absolute URL (Universal Resource Locator) and the 'href' attribute.</p>

   <pre>
<code>&lt;a href=&quot;http://www.w3schools.com/html/html_links.asp&quot;&gt;Link to w3schools.com&lt;/a&gt;</code></pre>

   <div class="example">
      <a href="http://www.w3schools.com/html/html_links.asp">Link to w3schools.com</a>
   </div>
   <p>Using a relative URL to an image looks like this:</p>

   <pre>
<code>&lt;a href=&quot;https://bartonphillips.net/images/msfree.png&quot;&gt;View the MS Free Image&lt;/a&gt;</code></pre>

   <div class="example">
      <a href="https://bartonphillips.net/images/msfree.png">View the MS Free Image</a>
   </div>

   <p>The absolute URL in the first example links to an external page on another server. The relative URL in the second example
      links to an image on our server.</p>
   <p>A page anchor lets you specify a location on a page and then return to that location using a link later in your page. Here I
      have placed an anchor at the top of this page it looks like this:</p>

   <pre>
<code>&lt;a name=&quot;top-of-page&gt;&lt;/a&gt;</code></pre>
   <p>You can also use the 'id' attribute as the link address like this:</p>
   <pre>
<code>&lt;div id=&quot;top-of-page2&gt;This is the top of the page&lt;/div&gt;</code></pre>

   <p>I can put a link here to go to the top of the page like this:</p>

   <pre>
<code>&lt;a href=&quot;#top-of-page&quot;&gt;Go To The Top Of This Page&lt;/a&gt;</code></pre>

   <pre>
<code>&lt;a href=&quot;#top-of-page2&quot;&gt;Go To The Top Of Page DIV&lt;/a&gt;</code></pre>

   <div class="example">
      <a href="#top-of-page">Go To The Top Of This Page</a>
   </div>

   <div class="example">
      <a href="#top-of-page2">Go To The Top Of Page DIV</a>
   </div>

   <p>If you click on the above examples you will move to the top of the page.</p>
   <p>For further information about HTML Anchors and Links go to <a href="http://www.w3schools.com/html/html_links.asp">w3school.com HTML</a>.</p>
   <hr>

   <a id='showsource' href="howtowritehtml.php?page=showsource">Show source code of this file</a>
   <div id='showresults'></div>

   <p>For further information about HTML go to <a href="http://www.w3schools.com/html/default.asp">w3school.com HTML Links</a>.</p>
</div>
<hr>

<h2>Now You Can Practice</h2>

<p>I hope this tutorial has been helpfull. You can try out some HTML by entering text in the 'textarea' below and
   then clicking the 'Render' button.</p>
    
<form id="previewform" action="" method="post" style="border: 1px solid black; padding: 5px;">
   <h2>Enter HTML Into Text Area</h2>

   <textarea id="inputtextarea" rows="10" cols="40" style="width: 99.5%; height: 200px">&lt;h1&gt;This Is A Text&lt;/h1&gt;
&lt;p&gt;This is a paragraph and here is some &lt;b&gt;Bold text&lt;/b&gt;
  and some &lt;i&gt;italic text&lt;/i&gt; and some
  &lt;span style=&quot;color: red&quot;&gt;red text&lt;/span&gt;.
&lt;/p&gt;
&lt;table border=&quot;1&quot;&gt;
  &lt;tr&gt;&lt;th&gt;Column One&lt;/th&gt;&lt;th&gt;Column Two&lt;/th&gt;&lt;/tr&gt;
  &lt;tr&gt;&lt;td&gt;Date 1&lt;/td&gt;&lt;td&gt;Date 2&lt;/td&gt;&lt;/tr&gt;
  &lt;tr&gt;&lt;td&gt;Date 3&lt;/td&gt;&lt;td&gt;Data 4&lt;/td&gt;&lt;/tr&gt;
&lt;/table&gt;
&lt;ol&gt;
  &lt;li&gt;This is a bullet list. This the first bullet&lt;/li&gt;
  &lt;li&gt;This is the second bullet&lt;/li&gt;
&lt;/ol&gt;
   </textarea>
   <div id="inputs" style="text-align: center; margin-top: 10px;">
      <input type="button" value="Render" id="render" style="display: none;color: green; font-size: 20pt;"/>
      <input type="button" value="Stop Auto Preview" id="autopreview" style="color: red; font-size: 20pt;"/>
   </div>
</form>

<h2>Preview Area</h2>
<div id="preview" style="background-color: white; border: 1px solid black; padding: 5px; overflow: auto; height: 200px;"></div>
<hr>
<div id="otherarticles">
  <p>Other articles in this series:</p>
  <ul>
    <li><a href="historyofinternet.php">The History of the Internet</a></li>
    <li><a href="howtheinternetworks.php">How the Internet Works</a></li>
    <li><a href="buildawebsite.php">So You Want to Build a Website</a></li>
  </ul>
</div>
<hr>
$footer;
EOF;

