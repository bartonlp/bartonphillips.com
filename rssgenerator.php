<?php
$_site = require_once(getenv("HOME")."/includes/siteautoload.class.php");
$S = new $_site['className']($_site);
$h->title = "RSS Generator Software";
$h->banner = "<h1>RSS Generator</h1>";
$top = $S->getPageTop($h);
$footer = $S->getFooter("<hr/>");
echo <<<EOF
$top
<hr/>
<p>
   I have written a Perl program that makes generating RSS feeds
   pretty easy. It lets you add markup to your html or php files and
   then generate an xml file in RSS format. Here is an example of how
   the markup looks:</p>
   <ul style="list-style: none; font-size: small;">
      <li>&lt;!-- &lt;rssfeed&gt; --&gt;</li>
      <li>&lt;h2&gt;This is the title of my article&lt;/h2&gt;</li>
      <li> &lt;a name=&quot;anchor-to-my-article&quot;&gt;&lt;/a&gt;</li>
      <li>&lt;p&gt;Here is the text for my article. It can have images
      links etc.&lt;/p&gt;</li>
      <li>&lt;!-- &lt;/rssfeed&gt; --&gt;</li>
   </ul>

<p>Once the markup is in the page run the Perl program:</p>
   <ul style="list-style: none; font-size: small;">
      <li>rssfeed.pl</li>
   </ul>
<p>The default setting, which can be edited in the configuration
   section of the <b>rssfeed.pl</b> file will create a rssfeed.xml
   file (or whatever you have named it.) Now all you need to do is
   place a link in the <b>head</b> section of your web page like
   this:</p>
   <ul style="list-style: none; font-size: small;">
      <li>&lt;link rel=&quot;alternate&quot;
         type=&quot;application/rss+xml&quot; title=&quot;RSS&quot;
        href=&quot;/rssfeed.xml&quot; /&gt;</li>
   </ul>

<p>or add a link in your code like this:</p>
   <ul style="list-style: none; font-size: small;">
      <li>&lt;p&gt;
         Subscribe to the My News RSS feed:<br/>
         &lt;a href=&quot;/rssfeed.xml&quot;&gt; &lt;img
         src=&quot;images/xmlbuttonorange.gif&quot; alt=&quot;My RSS
         feed&quot;
         border=&quot;0&quot;&gt;&lt;/a&gt;
      &lt;/p&gt;</li>
   </ul>

<p>The RSS Generator file is rssfeed.pl <a
   href="http://www.bartonphillips.com/download.php?file=rssfeed.pl.example">Download File</a>
</p>
<!-- </rssfeed> -->
<hr/>

<a name="newversion"></a>
<h3>Latest version of RSS Generator</h3>
<!-- <rssfeed date='Mon, 4 May 2009 00:57:00 GMT' title="New Updated Version of RSS Gnerator" anchor="newversion"> -->
<p>Now you can add more attributes to the &lt;rssfeed&gt; tag. The new
   attributes are:</p>
   <ul>
      <li>url</li>
      <li>page</li>
      <li>anchor</li>
      <li>title</li>
      <li>date (date is not actually new)</li>
      <li>noesc</li>
   </ul>
<p>These attributes let you add information that is not in the markup.
Also it makes it easy to have a text file that has your RSS
information and use the <b>url</b>, <b>page</b> and <b>anchor</b>
attributes to specify the target for the article. Here is an example
of how this could work:</p>
   <ul style="list-style: none; font-size: small;">
      <li>&lt;rssfeed title=&quot;My new feed&quot;
         url=&quot;http://www.someplace.com&quot;
         page=&quot;newpage.html&quot;&gt;</li>
      <li>&lt;p&gt;This is my new RSS stuff.&lt;/p&gt;</li>
      <li>&lt;h2&gt;This h2 is not the title because we have a title
         attribute&lt;/h2&gt;</li>
      <li>&lt;/rssfeed&gt;</li>
   </ul>   
<p>This could be placed in a separate text file, or within a <i>html</i>
file if you placed
<i>html</i> comment marks around the rssfeed tags.</p>

<p>The <b>noesc</b> attribute takes not agruments. If present it keeps
   the section from being html encoded, that is, &lt; and &gt; are not
   turned into &amp;lt; and &amp;gt;.</p>

<p>The perl file <b>rssfeed.pl</b> has documentation in the code and
   also via options -h, --help, -m, or --man at the command line.</p>
<p><b>Note:</b> the application requires the following packages:</p>
   <ul>
      <li>File::Copy</li>
      <li>File::Basename</li>
      <li>File::Spec</li>
      <li>File::Spec::Link</li>
      <li>Getopt::Long</li>
      <li>Pod::Usage</li>
   </ul>
<p>View the <a href="http://www.bartonphillips.com/rssfeed.man.php">man</a></p>
<!-- </rssfeed> -->

<p>All of our code is released under the MIT and GPL <a
href="License/index.php">License</a></p>
$footer
EOF;
