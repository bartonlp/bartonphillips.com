<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // takes an array if you want to change defaults

$h->title = "So You Want to Build a Website";
$h->banner = "<h1 class='center'>So You Want to Build a Website</h1><hr>";
$h->script = <<<EOF
<!-- From: http://updates.html5rocks.com/2011/08/Saving-generated-files-on-the-client-side
https://github.com/eligrey/FileSaver.js -->
<script src="https://bartonphillips.net/js/FileSaver.js"></script>
<script>
jQuery(document).ready(function($) {
  // Submit button clicked.

  $("#submit").click(function(e) {
    var txt = $("#editarea").val();
    console.log("txt", txt);
    var blob = new Blob([txt], {type: "text/plain;charset=utf-8"});
    try {
      saveAs(blob, "download.html");
      $("#submit-results").html("<h3 style='color: green'>Submit OK</h3>");
    } catch(err) {
      console.log(err);
      $("#submit-results").html("<h3 style='color: red'>Submit Railed</h3>");
    }
    return false;
  });

});
</script>
EOF;
$h->css = <<<EOF
<style>
#editarea { /* textarea */
  width: 100%;
  height: 300px;
  font-size: 1rem;
  padding: .5rem;
}
#submit { /* Submit Button */
  font-size: 1rem;
  border-radius: .3rem;
}
.addstuff {
  color: red;
}
#tbl td {
  padding-left: 20px;
}
#tbl td:first-child {
  border-left: 5px solid #ccc;
}
</style>
EOF;

$h->meta = "<meta name='Editor' content='Bonnie Burch'>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h2>Building a website is a four step process</h2>
<ol>
  <li>Create the HTML pages.</li>
  <li>Get a domain name.</li>
  <li>Get an ISP (Internet Service Provider) to host the site.</li>
  <li>Load the HTML pages onto the server.</li>
</ol>

<h2>Step One</h2>

<p>Of these four steps the first is really the simplest. HTML is easy to create; just look at my
  <a href="howtowritehtml.php">tutorial</a>. The tutorial will give
  the basics. In addition you will need a little boilerplate at the top of the page. The following is
  some example text that should be enough to start with:</p>

<table id="tbl">
  <tr><td></td><td>

    <pre>
&lt;!DOCTYPE html&gt;
&lt;head&gt;
  &lt;title&gt;<span class="addstuff">Add Your Page Title Here</span>&lt;/title&gt;
  &lt;meta charset='utf-8'/&gt;
  &lt;meta name=&quot;Author&quot;
     content=&quot;<span class="addstuff">Add Your Name Here</span>&quot;/&gt;
  &lt;meta name=&quot;description&quot;
     content=&quot;<span class="addstuff">Add a Description of Your Page Here</span>&quot;/&gt;
&lt;/head&gt;
&lt;body&gt;
<span class="addstuff">Put Your Html For The Page Here</span>
&lt;/body&gt;
&lt;/html&gt;
    </pre>
    </td></tr>
</table>

<p>Just copy this simple boilerplate text and add the information in RED using your text editor.
  Do NOT use a word processor, use a text editor like 'Notepad' on Windows or 'nano' on Linux.</p>

<p>Or use the 'textarea' in the form below; edit it and then save the text by clicking submit. You
  can add all of your HTML with this form.
  The submit dialog will ask you for the location where you want the file (download.html) to reside.</p>

<form>
<textarea id="editarea">
&lt;!DOCTYPE html&gt;
&lt;head&gt;
  &lt;title&gt;Add Your Page Title Here&lt;/title&gt;
  &lt;meta charset='utf-8'&gt;
  &lt;meta name=&quot;Author&quot;
     content=&quot;Add Your Name Here&quot;&gt;
  &lt;meta name=&quot;description&quot;
     content=&quot;Add a Description of Your Page Here&quot;&gt;
&lt;/head&gt;
&lt;body&gt;
Put Your Html For The Page Here
&lt;/body&gt;
&lt;/html&gt;
</textarea>
<br>
<button id="submit">Submit</button>
<div id="submit-results"></div>
</form>

<h2>Step Two</h2>

<p>It is probably best to find an ISP (Internet Service Provider) and then get your domain name from
  them as it is often easier if everything is handled by one organization. That way there is less
  finger-pointing. I have had very good results with the following ISPs:</p>
<ul>
  <li><a href="http://lamphost.net">Lamphost.net</a> (in San Diego CA).
    They are very easy to work with and provide excellent support and service.
    They register via Tucows. Domain registration is $15/year and web hosting is $10/month.</li>
  <li><a href="http://www.digitalocean.com">DigitalOcean.com</a>.
    This ISP is my current favorite.
    I host www.bartonphillips.com, www.bartonphillips.net,
    www.bartonlp.com, www.bartonlp.org, www.applitec.com,
    www.allnaturalcleaningcompany.com and www.newbern-nc.info at DigitalOcean.
    I have a 'Virtual Private Servers' where I have FULL adminstrator (root) access. The servers are fast, economical and
    secure. These servers do require a good understanding of system administration however; there is
    very little hand-holding. The hosting costs between $5 and $10/month.<br>
    The above domains are registered with <a href="www.hover.com">hover.com</a>,
    GoDaddy and Tucows for about $15/year each.</li>
  <li>Another approach is to use <a href="https://github.com">GitHub</a>. You can sign up for free and set up a repository.
    Then follow their instructions to use their server as your website. All it takes is a little reading and you should be
    able to get your website on the air. You can even get your own domain name and use that; otherwise your webpage will be at
    https://<b>YourName</b>.github.io/<b>NameOfYourRepository</b>. This is a good way to get your webpage on the cloud without any expense.</li>
</ul>

<p>There are thousands of ISPs from very very big to very small. Any of those above should be pretty
  safe.</p>

<p>Go to the website of any one of the above and you can see what domain names are
  available. For example, at lamphost.net click on their <b>Hosting Extras</b> navigator at the top
  of the page and select <b>Domain Names</b>. Then select <b>Register Your Domain Name</b>. Enter
  the domain name you would like. Click the submit button and they will tell you if it is available
  and what it will cost. From there just select the number of years you want and click
  <i>continue</i>. It is that simple. All of the ISPs have similar facilities.</p>

<h2>Step Three</h2>

<p>Setting up a hosting plan is almost as simple as getting your domain name. Most ISPs have online
instruction describing the process. You want a <b>Shared Hosting</b> plan. That means you will be
sharing the server with a number of other people. You will not be able to interact with them and
they will not be able to interact with you. What it means, however, is that the ISP will make sure
that everything on the server is secure and that your data is always safe and backed up. It also
possibly means that once in a while things may slow down if someone else's traffic goes way up. There
are other types of dedicated server plans but they are more expensive and beginners would not
know how to use them anyhow.
Almost all ISPs have monthly and yearly plans. To start off, I would suggest the monthly
plan as the difference between the two is not much and with a monthly plan you are not tied down.
Wait until you are sure 1) you like it and 2) you want to maintain a web presence.</p>

<h2>Step Four</h2>

<p>All of the above is pretty simple as long as you have a credit card. Now things get a bit
more complex. Once you have ordered your domain name and host, your ISP will send you an email
with the important information. Here is where it may get a bit confusing as the information is
usually pretty terse and esoteric. All three of the ISPs I have listed have pretty good phone
support. I think without a doubt Lamphost.net has the best but the other two are also pretty good.
Don't hesitate to CALL if you get stuck or just don't understand what they are talking about.</p>

<p>In a nutshell, what your ISP should send you in the email is:</p>

<ul>
  <li>A Username and Password for your account. You will use these to log in on their website.
  Usually the login is somewhere near the top of the screen.</li>
  <li>The IP address of your domain.</li>
  <li>An FTP address for your server or instruction about setting up an account
    (maybe you have to figure that all out).</li>
  <li>An email address or instructions about setting up an account (like above).</li>
  <li>Maybe an ssh account login (more about ssh later).</li>
  <li>Your DNS addresses (more on this later too).</li>
</ul>

<p>If you didn't register your domain name with the same ISP that is doing your Shared Hosting, then
  you may have some more work to do before anything will work.</p>
<p>You will need the DNS addresses your ISP sent you.
  There are usaually two domain names that look something like
  <b>ns1.something.com</b> and <b>ns2.something.com</b>. You will need to go to the website of the
  &quot;Registrar&quot; for your domain name. They will also have a way for you to log in (usually at
  the top of the screen) and should have sent you a welcoming email with your account
  name and password. You will need to get to a place where you can enter the two DNS
  addresses your ISP sent. This may require some reading and if all else fails, call their
  customer support line. The two addresses your ISP sent are used to connect your domain name to
  your ISP's DNS server. Hopefully you registered your domain name at the same ISP that is providing
  your web hosting and they have done all this for you.</p>

<p>Once you have your Username and Password you can access your ISP's <i>Control Panel</i> where you
  can (if you know what you are doing) set up your site. At this point be prepared to do some
  reading, a lot of reading. Every ISP is different so even those of you who have done this many times
  before get stumped. Remember, you are paying for support so CALL if you don't understand
  or if things just don't seem to work the way they are described. Sometimes it is your fault but a
  lot of the time their site just isn't working right (hard to believe but true).</p>

<p>You may need to set up or enable FTP and email. Some ISPs have that set up for you in a default
  configuration and some don't. Using the <i>Control Panel</i> try to find information about your
  FTP account because this is the easiest way to get the web pages you created in step one onto the
  web server.</p>

<p>Initially your domain probably points to a page that says something like &quot;Under
  Construction&quot;. You need to upload an &quot;html&quot; file to the server and name
  it &quot;index.html&quot;. Use your FTP account to do this. Most browsers let you log in to the FTP
  account by simply entering &quot;ftp://your-domain-name.extension&quot;. After a second there will
  appear a dialog box that asks for your <i>Username</i> and <i>Password</i>. If your ISP has sent
  you this information in its email (lucky you), just enter it. If you had to set up the FTP account
  via the ISP's <i>Control Panel</i> be sure you use the username which may or may not contain the
  full email address like for example: <b>myname@thisISP.com</b>. Or it might just be just
  <b>myname</b>. Experiment a little if it doesn't work right off.</p>

<p>Once you get FTP access, find the directory where your website reside. This may be the first
  directory you come to in the FTP screen or your ISP may have given you a further path to follow
  (or maybe you are on your own). Look around and examine the various directory. Somewhere you should
  find a file called <b>index.html</b> or <b>default.html</b> (the html may also just be htm). You
  want to load your own home page file and call it <b>index.html</b>. Once you have uploaded your
  home page, go to a new tab in your browser and enter your URL (domain name) and see if you see your
  home page.</p>

<p>If all goes well, you are on your way. Now just write some more HTML pages, add some links and
  images and you are a webpage designer. If all does not go well, get on the phone to technical
  support and they WILL help you.</p>

<h2>After the Party</h2>

<p>After you have your first page up and running, it is time to think of some other things. Remember
we said something about &quot;ssh&quot; account. Well, ssh stands for secure shell and it is a way
for you to log into the server and do anything you can do on a command line. If you fear the command
line as most Windows users do, you can forget about this. But if you really want to enjoy and learn,
you should look into using ssh. If you are working on a real computer operating system, that is
Linux or Max OS X, you can also use &quot;sshfs&quot; which lets you mount the remote filesystem on
your local computer and use your favorite text editor to work on your pages in vitro (so to speak).
It really makes working on a remote system fun and easy. You will never want to use FTP again.
</p>

<p>Once you have mastered HTML you may want to get a <i>WYSIWYG</i> (What You See Is What You Get)
  editor like <b>Dreamweaver</b>. I personally think that this is cheating and that WYSIWYG editors
  produce really bad bloted HTML. If you want nice tight HTML, you really need to code it
  yourself.</p>

<p>In reality there is actually a fifth step. That step never ends: It is maintenance and
enhancement. At some point you may want to move from the <b>static</b> website you built to a more
<b>dynamic</b> exciting page. This will require some client programming with
<a href="http://en.wikipedia.org/wiki/JavaScript">Javascript</a> and probably some server back-end
programming with <a href="http://www.php.net">PHP</a>.  This is when all of this really
becomes fun and better than any video game. You can make your website as interesting and exciting as
you want -- the sky is the limit. Good luck and have fun.</p>

<hr>

<div id="otherarticles">
  <p>Other articles in this series:</p>
  <ul>
    <li><a href="historyofinternet.php">The History of the Internet</a></li>
    <li><a href="howtheinternetworks.php">How the Internet Works</a></li>
    <li><a href="howtowritehtml.php">How to Write HTML</a></li>
  </ul>
</div>
<hr>

$footer
EOF;

