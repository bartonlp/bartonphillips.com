<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");


$S = new Blp;
$h->title = "Setup Canon MX330 Under Ubuntu Linux";
$h->banner = "<h1>Setup Canon MX330 Under Ubuntu Linux</h1>";
$top = $S->getPageTop($h);
$footer = $S->getFooter("<hr/>");

echo <<<EOF
$top
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

<p>Download the two tar.gz file that contain the dep files. Untar them
   and install them. On Ubuntu you can just open the location in
   Nautilus and click on the deb files (other methods will also work).
   I rebooted after the installs which I don't think is actually
   necessary. After the print driver is installed power cycle the
   MX330 and Ubuntu should start the &quot;CUPS configuration
   tool&quot; (system-config-printer 1.1.3). You should now find the
   MX330 under the &quot;Canon&quot; menu item.</p>
<p>For the scanner the deb installs a program called
   &quot;scangearmp&quot; in /usr/bin, along with some libraries. I
   chose to add <b>scangearmp</b> to my <b>Application/Graphics</b>
   menu. Once the scangearmp program is installed you can use it to
   scan stuff. The program is also available from GIMP via the
   <b>File/create/</b> menu.</p>
<p>I have so far had good luck with the MX330. I think Canon could
   have provided more documentation about the drivers and how to set
   them up etc. It was actually pretty simple once I had done it, but
   it took some trial and error work. I hope this helps you avoid some
   of the trial and error stuff.</p>
<hr/>
<h2>May 19, 2010 Update</h2>
<p>Thanks to Bennett Kanuka here is a solution if you are running a 64bit version of Ubuntu:</p>
<pre><code>sudo dpkg -i --force-architecture cnijfilter-common_3.10-1_i386.deb
sudo dpkg -i --force-architecture cnijfilter-mx330series_3.10-1_i386.deb
</code></pre>
<p>The key here is the <code>--force-arhetecture</code>. Thanks Bennett.</p>
<hr/>
<h2 style="color: red">November 16, 2013 Update</h2>
<p>I have not been able to get the latest drivers to work with my Linux Mint 15 64bit installation.
I have spend quite a bit of time but the 32bit drivers require 32bit versions of some GIMP
libraries which I have not been able to successfuly install.</p>
<p>At this point I would NOT recommend the MX330. I really wish Cannon and others would support
Linux or go out of business.</p>
<p>If anyone has had success installing both the print and scanner drivers PLEASE let me know as
my printer is pretty much worthless now.</p>
<hr/>
$footer
EOF;
?>