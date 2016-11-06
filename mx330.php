<?php
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);
$h->title = "Setup Canon MX330 Under Ubuntu Linux";
$h->banner = "<h1>Setup Canon MX330 Under Ubuntu Linux</h1>";
list($top, $footer) = $S->getPageTopBottom($h, '<hr>');

echo <<<EOF
$top
<hr>
<h2 style="color: red">April 25, 2014 Update</h2>
<h3>If you have not bought a Canon yet, DON'T. If you have send it back.</h3>

<p>I upgraded to <b>Linux Mint 16 64bit</b> and the Canon drivers would not install at all. So
I finally gave up on the mx330 and bought an HP Deskjet 8600 e-All-In-One wireless printer.  HP
have very good third-party Linux support. There is even a link from the main HP website to <a
href="http://hplipopensource.com/hplip-web/index.html">http://hplipopensource.com/hplip-web</a>
where you can download the HPLIP driver package. The link takes you to a page that has basically
every pinter HP has ever made (at least it seems that way.) You follow a couple of additional links
that ask for OS type, printer type, and specific printer information. Finally you will download the
hhplip-3.14.4.run installer. I made the file executable and ran it as root and after several minutes
of question answering and watching the screen the drive was installed and everything worked
perfectly.</p>

<p>The wireless feature makes the printer very easy to use from all of my computers and even from
my tablet on the road -- though I don't really know why I would want to print something while on the
road). The 8600 also has an embedded Web Interface that will work form almost any modern browser on
any OS even cell phones and tablets -- can't ask for much more than that.</p>

<p>If you want a printer that is going to work with Linux get an HP NOT a Canon. I had nothing but
problems with the Canon and will <b>NEVER</b> buy something from them again! By the way I even
had problems installing drivers on Window$ 7 on my laptop! With the HP no problems anywhere.</p>

<hr>

<h2 style="color: red">November 16, 2013 Update</h2>

<p>I have not been able to get the latest drivers to work with my <b>Linux Mint 15 64bit</b>
installation.  I have spend quite a bit of time but the 32bit drivers require 32bit versions of some
GIMP libraries which I have not been able to successfuly install.</p> <p>At this point I would NOT
recommend the MX330. I really wish Canon and others would support Linux or go out of business.</p>

<p>If anyone has had success installing both the print and scanner drivers PLEASE let me know as my
printer is pretty much worthless now.</p>

<p>Update: I finally got the printer working but not the scanner. I really don't know what I did to
get it working. It was kinda the &quot;infinite number of monkeys scenario&quot;.
I'll keep using the printer for a while as I don't need to scan very often.</p>

<hr/>

<h2>May 19, 2010 Update</h2>
<p>Thanks to Bennett Kanuka here is a solution if you are running a 64bit version of Ubuntu:</p>
<pre><code>sudo dpkg -i --force-architecture cnijfilter-common_3.10-1_i386.deb
sudo dpkg -i --force-architecture cnijfilter-mx330series_3.10-1_i386.deb
</code></pre>
<p>The key here is the <code>--force-arhetecture</code>. Thanks Bennett.</p>
<hr/>

<h2>July 3, 2009</h2>

<p>The Canon MX330 is a low cost All-In-One Printer, Scanner, Copier,
   and Fax Machine. It comes with a CD with Windows and Mac OSX
   drivers and documentation. Not much help for those of us who have
   sworn off Windows and don't own a Mac.</p>
<p>Fortunately Canon actually does supply drivers for the MX300 that
   work for Linux and Ubuntu/Debean. They are a bit hard to find and
   the installation is not documented at all as far as I could see. Here
   is a quick HOW TO. Where to find the drivers and how to install
   them.</p>
<p>First where are the drivers?</p>
<ul>
   <li><a
   href="http://software.canon-europe.com/software/0033558.asp">Printer
      Driver</a></li>
   <li><a
   href="http://software.canon-europe.com/software/0033574.asp">Scanner
      Driver and Scan Program</a></li>
</ul>

<p>Download the two tar.gz file that contain the dep files. Untar them and install them. On Ubuntu
you can just open the location in Nautilus and click on the deb files (other methods will also
work).  I rebooted after the installs which I don't think is actually necessary. After the print
driver is installed power cycle the MX330 and Ubuntu should start the &quot;CUPS configuration
tool&quot; (system-config-printer 1.1.3). You should now find the MX330 under the &quot;Canon&quot;
menu item.</p>

<p>For the scanner the deb installs a program called &quot;scangearmp&quot; in /usr/bin, along with
some libraries. I chose to add <b>scangearmp</b> to my <b>Application/Graphics</b> menu. Once the
scangearmp program is installed you can use it to scan stuff. The program is also available from
GIMP via the <b>File/create/</b> menu.</p>

<p>I have so far had good luck with the MX330. I think Canon could have provided more documentation
about the drivers and how to set them up etc. It was actually pretty simple once I had done it, but
it took some trial and error work. I hope this helps you avoid some of the trial and error
stuff.</p>

<hr/>
$footer
EOF;
