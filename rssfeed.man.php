<?php
$_site = require_once(getenv("HOME")."/includes/siteautoload.class.php");
$S = new $_site['className']($_site); // count page
$h->title = "rssfeed.pl - Generate RSS feed from web pages";
$h->banner = "<h1>Man Page for rssfeed.pl</h1>";
$top = $S->getPageTop($h);
$footer = $S->getFooter("<hr/>");
echo <<<EOF
$top
<hr/>
<!-- INDEX BEGIN -->
<div name="index">
<p><a name="__index__"></a></p>

<ul>

	<li><a href="#name">NAME</a></li>
	<li><a href="#synopsis">SYNOPSIS</a></li>
	<li><a href="#options">OPTIONS</a></li>
	<li><a href="#description">DESCRIPTION</a></li>
	<li><a href="#examples">EXAMPLES</a></li>
	<li><a href="#files">FILES</a></li>
	<li><a href="#see_also">SEE ALSO</a></li>
	<li><a href="#notes">NOTES</a></li>
	<li><a href="#bugs">BUGS</a></li>
	<li><a href="#author">AUTHOR</a></li>
</ul>

<hr name="index" />
</div>
<!-- INDEX END -->

<p>
</p>
<h1><a name="name">NAME</a></h1>
<pre>
 rssfeed.pl - Generate RSS feed from web pages</pre>
<p>
</p>
<hr />
<h1><a name="synopsis">SYNOPSIS</a></h1>
<p>option 1: rssfeed [&lt;newsfile&gt; [&lt;rssfile&gt;]]</p>
<pre>
  If a workfile name is given then that file is read instead of the
  filename given in the config section.
  If an rssfile name is given then output is written to that file.</pre>
<p>option 2: rssfeed [&lt;newsfilename&gt; [&lt;rssfilename&gt;]] &lt; &lt;newsfile&gt;</p>
<pre>
  newsfilename is optional if not present then output goes to workfile
  named in config section.
  If rssfilename is pressent that file is used for the rss output else
  from config section.</pre>
<p>option 3: cat xx | rssfeed [&lt;newsfilename&gt; [&lt;rssfilename&gt;]]</p>
<pre>
  like option 2 just from a pipe</pre>
<p>
</p>
<hr />
<h1><a name="options">OPTIONS</a></h1>
<p>-h --help</p>
<pre>
   Display help</pre>
<p>-m --man</p>
<pre>
   Display a full man page</pre>
<p>-c configfile --config=configfile</p>
<pre>
   use the named file as the configuration file. For example: rssfeed.pl --config=path/test.config</pre>
<p>-n --noesc</p>
<pre>
   do not escape &lt; or &gt;. If not set then &lt; = &amp;lt; and &gt; = &amp;gt;</pre>
<p>-r --resetdate</p>
<pre>
   do not use the value in date=&quot;...&quot; if it exists, instead use todays date-time.</pre>
<p>
</p>
<hr />
<h1><a name="description">DESCRIPTION</a></h1>
<p>This program reads a new (html) file and looks for &lt;rssfeed&gt; tags
that should be inside html comments like this:</p>
<pre>
   &lt;!-- &lt;rssfeed&gt; --&gt; some html &lt;!-- &lt;/rssfeed&gt; --&gt;</pre>
<p>Strictly speaking the rssfeed tag does not need to be inside
comments, also you can have:</p>
<pre>
   &lt;!-- &lt;rssfeed&gt; then html which is inside the comment &lt;/rssfeed&gt; --&gt;</pre>
<p>which lets you have code that does not appear on the web page.</p>
<p>This program extracts the &lt;h2&gt; element as the &lt;title&gt; element of the
rss.</p>
<p>If there is a &lt;a name=... tag the text of the name is appended
to the link with a so the link goes directly to the anchor.</p>
<p>The program creates a temp file news.php.rss which has the &lt;rssfeed&gt;
tag replaced with &lt;rssfeed date='...'&gt; which has the date this
program was run. If the news.php file has the date='...' attrubute on
the rssfeed tag then that date is used instead of the current date.
After the program is done it copies the news.php file to news.php.old
and then moves the news.php.rss file to replace news.php.</p>
<p>The &lt;rssfeed&gt; tag can take several other attributes:</p>
<pre>
  date=&quot;...&quot;     article date
  title=&quot;...&quot;    article title
  url=&quot;...&quot;      the base url of the target page
  page=&quot;...&quot;     the page file name
  anchor=&quot;...&quot;   the anchor name
  noesc          do not escape html codes</pre>
<p>each of these attributes takes the place of tag item between the &lt;rssfeed&gt; tag. For example:</p>
<pre>
  &lt;rssfeed url=&quot;http://www.xyz.com&quot; page=&quot;XYZ.php&quot; anchor=&quot;this&quot; title=&quot;XYZ test&quot; date=&quot;Sun, 26 Apr 2009 19:58:59 GMT&quot;&gt;
  &lt;h2&gt;Some text here&lt;/h2&gt;
  &lt;p&gt;Some more text as a description&lt;/p&gt;
  &lt;/rssfeed&gt;</pre>
<p>This section of code would produce the following &lt;item&gt; sectoin in the rssfeed.xml file:</p>
<pre>
  &lt;item&gt;
    &lt;title&gt;XYZ test&lt;/title&gt;
    &lt;link&gt;http://www.xyz.com/XYZ.php#this&lt;/link&gt;
    &lt;description&gt;&lt;h2&gt;Some text here&lt;/h2&gt;&lt;p&gt;Some more text as a description&lt;/p&gt; &lt;/description&gt;
    &lt;pubDate&gt;Sun, 26 Apr 2009 19:58:59 GMT&lt;/pubDate&gt;
  &lt;/item&gt;</pre>
<p>The &lt;h2&gt; tag is ignored as a title if the title attribute is provided. The same goes for the other attributes. The link
attribute takes the place of the default link set in the configuration section, this lets you have &lt;rssfeed&gt; tags in
one file that reference another file or site.</p>
<p>
</p>
<hr />
<h1><a name="examples">EXAMPLES</a></h1>
<p>rssfeed.pl</p>
<pre>
   The default behavior, the files mentioned in the configuration file or the defaults are used.</pre>
<p>rssfeed.pl def.html</p>
<pre>
  The file 'def.html' is read instead of the 'newsfile' mentioned in the configuration file or default. The file 'def.html' is
  updated and a 'def.html.old' is the backup. The rss feed goes into the file mentioned in the configuration.</pre>
<p>rssfeed.pl def.html abc.xml</p>
<pre>
  Like above but the rss feed goes into 'abc.xml'.</pre>
<p>rssfeed.pl xyz.html &lt; def.html</p>
<pre>
  The file to be parsed is 'def.html', the rss feed output goes to the file mentioned in the configuration file or defaults,
  the new html goesss to 'xyz.html'.</pre>
<p>rssfeed.pl xyz.html abc.xml &lt; def.html</p>
<pre>
  The file to be parsed is 'def.html', the rss feed output will go to 'abc.xml', the new html goes to 'xyz.html'.</pre>
<p>wget -O - http://localhost/def.php | rssfeed.pl</p>
<pre>
  If you have rssfeed tags that at generated dynamically you can pipe the output from the webpage to rssfeed.pl.
  Assumming the configuration file or defaults are set to 'newsfile=webpath/def.php', 'rssfile=webpath/abc.xml'
  the rss output would go to webpath/abc.xml, the file webpath/def.php would be updated and a backup file
  webpath/def.php.old would be created.</pre>
<p>
</p>
<hr />
<h1><a name="files">FILES</a></h1>
<p>rssfeed.config             default configuration file. Should be in the same directory as the rssfeed.pl.
                           Can be created by cutting and pasting the default configuration from the script
                           and changing the variables to fit your site.</p>
<p>
</p>
<hr />
<h1><a name="see_also">SEE ALSO</a></h1>
<p><a href="http://www.bartonphillips.com">http://www.bartonphillips.com</a></p>
<p>
</p>
<hr />
<h1><a name="notes">NOTES</a></h1>
<p>The &lt;rssfeed ...&gt; can be split over several lines; however, the ending MUST be on a line by itself. If the &lt;rssfeed&gt; tag
is inside a comment the end comment can be on the same line as the ending &gt; of the tag.</p>
<p>This is OK:</p>
<pre>
   &lt;--
   &lt;rssfeed
   title=&quot;Hi There&quot;&gt;
   --&gt;</pre>
<p>This is NOT OK:</p>
<pre>
   &lt;-- &lt;rssfeed title=&quot;Hi There&quot;&gt; --&gt; &lt;p&gt;Some html on the same line&lt;/p&gt;</pre>
<p>I guess this could be thought of as a BUG but I like to think of it as a feature:)</p>
<p>
</p>
<hr />
<h1><a name="bugs">BUGS</a></h1>
<p>Probably, if you find any please let me know at the email addresses below. Thanks.</p>
<p>
</p>
<hr />
<h1><a name="author">AUTHOR</a></h1>
<p>Barton Phillips</p>
<ul>
<li><strong><a name="barton_bartonphillips_com" class="item"><a href="mailto:barton@bartonphillips.com">barton@bartonphillips.com</a></a></strong>

</li>
<li><strong><a name="bartonphillips_gmail_com" class="item"><a href="mailto:bartonphillips@gmail.com">bartonphillips@gmail.com</a></a></strong>

</li>
<li><strong><a name="http_www_bartonphillips_com" class="item"><a href="http://www.bartonphillips.com">http://www.bartonphillips.com</a></a></strong>

</li>
</ul>
$footer
EOF;
