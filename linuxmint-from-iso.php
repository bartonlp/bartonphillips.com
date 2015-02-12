<?php
// How to load LinuxMint 17 from iso
require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp; // takes an array if you want to change defaults

$h->title = "Load LinuxMint 17 from ISO";
$h->banner = "<h1 class='center'>How to Load LinuxMint via ISO from Disk</h1>";
$h->css =<<<EOF
  <style>
#update {
  border: 1px solid black;
  background-color: pink;
  color: black;
  padding: 1em;
}
.update {
  color: red;
  font-size: .5em;
}
pre {
  font-size: .7em;
  overflow: scroll;
  padding: .5em;
  border-left: .5em solid gray;
  background-color: #E5E5E5;
}
code {
  font-size: .7em;
  color: #4D4D4D;
}
b {
  color: #4d4d4d;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);
echo <<<EOF
$top
<div id="update">
<p>
<span class='update'>&lt;update 2015-01-13&gt;</span><br>
I originally wrote this for Linux Mint 15 but have used the same
instruction to upgraded to Linux Mint 16, 17 and 17.1 without any problems.
Just change the Mint release number and everything should work. In fact this should
continue to work for susequent versions
unless Linux Mint changes its ISO filesystem structure significantly.<br>
<span class='update'>&lt;/update 2015-01-13&gt;</span></p>
</div>

<p>Instead of burning a CD or using a USB flash stick you can install Linux Mint 15 (and
most other Linux distributions) from a hard disk. All you need is a small (about 2 GB)
extra partition somewhere.  This example is for 'Linux Mint 15 Mate 64'. The arguments
you would use for other distribution might (and probably will be) different.</p>

<p>This method uses GRUB2 but I believe GRUB will also work with different syntax (not
shown here).</p>

<p>You should edit the GRUB2 configruation rather than editing
<b>/boot/grub/grub.cfg</b>. The
configuration files are at <b>/etc/grub.d/</b> and the file you want to edit is
<b>40_custom</b>.</p>

<p>There will probably be nothing in that file to begin with other than the first five
lines below.  Add the following lines after the header that was there:</p>

<pre>
#!/bin/sh
exec tail -n +3 \$0
# This file provides an easy way to add custom menu entries.  Simply type the
# menu entries you want to add after this comment.  Be careful not to change
# the 'exec tail' line above.

menuentry "Linux Mint 15 Mate ISO" {
 loopback loop (hd1,msdos5)/barton/Downloads/linuxmint-15-mate-dvd-64bit.iso
 linux (loop)/casper/vmlinuz file=/cdrom/preseed/mint.seed boot=casper initrd=/casper/initrd.lz iso-scan/filename=/barton/Downloads/linuxmint-15-mate-dvd-64bit.iso noeject noprompt splash --
 initrd (loop)/casper/initrd.lz
}
</pre>

<p>The first five lines are what is usually in the file to start. Add the other five
lines and then edit them to reflect your environment. You will need to edit the lines
that start with 'loopback' and 'linux'. On my system I have a seperate partition for my
<b>/home</b> directory.  This makes it much easier when it comes time to upgrade as my
<b>home</b> directory is safe and I can also put my ISO somewhere on that partition.</p>

<pre>
(hd1,msdos5)/barton/Downloads/linuxmint-15-mate-dvd-64bit.iso
</pre>

<p><i>(hd1,msdos5)</i> means the second hard disk in my system (I have two internal hard
drives one with 320 GB and one with 500 GB). <i>hd1</i> is the 500 GB drive which has
five partitions. My <b>home</b> directory is on partition five (msdos5).
In the <b>/home</b> directory is my home <b>/home/barton/</b> and the 'Linux Mint ISO'
is in the <b>/home/barton/Downloads</b> directory.<br>
<span class='update'>&lt;update 2015-01-13&gt;</span><br>If your disk is GPT
(GUID Partition Table) rather than MSDOS you will need to use <i>(hd1,gpt1)</i>
instead. If you run '<code>sudo parted -l</code>' you will see either
'Partition Table: gpt' or 'Partition Table: msdos'.<br>
<span class='update'>&lt;/update 2015-01-13&gt;</span></p>

<p>The ISO is only about one GB so you really don't need a very big partition.
Unfortunatly you do need a partition other than the one where you will install the new
OS. If you don't have a seperate partition for <b>/home</b> you can use 'parted'
(<code>sudo apt-get install parted</code>) or some other utility to create a
little partition, 2 GB is plenty.</p>

<p>The next line which starts with 'linux' only needs the section<br>
<i>iso-scan/filename=/barton/Downloads/linuxmint-15-mate-dvd-64bit.iso</i><br>
changed to match where your ISO is, which is the same as the line above without the
<i>(h1,msdos5)</i> part.</p>

<p>Once you have the new code added to the <b>40_custom</b> file you need to update the
<b>grub.cfg</b> which is probably in your <b>/boot/grub</b> directory. Run
'<code>sudo grub-mkconfig --output=filename</code>' or you can just do
'<code>sudo grub-mkconfig >filename</code>'
as without the option the output goes to stdout. I would create a file somewhere other
than <b>/boot/grub</b> so I could look at the output first and make sure it is OK.</p>

<p>Once you think the output of OK you can move it to the location of <b>grub.cfg</b>
(usually at <b>/boot/grub</b>).
You might want to make a backup of the original file (or not, up to you).</p>

<p>Now it is time to reboot your system.</p>

<p><strong>Oh, you might want to backup anything you don't
want to lose before you go any further.</strong><br>
Some things to think about backing up are things in <b>/etc</b> that you
have customized, your <b>/var/www</b>, <b>/var/mail</b> and any database files if you
have them, and anything else that is on the partition where the new OS is going that you
don't want to lose. If your <b>/home</b> directory is on that partition you should
probably back it up also.</p>

<p>When you reboot select the new 'Linux Mint 15 Mate ISO' entry which brings up the
'Live CD' from which you can install the new OS.</p>

<p><strong>But, maybe it doesn't work.</strong><br>You can use the GRUB edit facilities and command line to
look to see if you have the drive and partition stuff correct. Using the 'c' option on
the first GRUB menue you can look at the drive where you think your ISO is. Type <code>ls
(h1,msdos5)</code>, for example, and you should see the files and directories. If you
type the full path to your ISO you should see it. If not then you got the drive and
partition wrong. Use the 'ls' command and find your ISO using other disk and partition
values.  Remember the 'hd' starts at zero not one, so the first disk drive is 'hd0'.
Partitions start at one not zero, so '(hd1,msdos1) means the second disk drive on the
system and the first parition ('consistency is the hobgoblin of small minds').</p>

<p>If you still can't get the boot to work reboot your old OS and review everything and
maybe do some 'google' searches. This all worked for me for the last three Linux Mint
releases and two Ubuntu releases before that. Note that some the the arguments have
changed on different releases. These arguments work for 'Linux Mint 15 Mate 64'
<span class='update'>&lt;update 2015-01-13&gt;</span>
16, 17 and now 17.1
<span class='update'>&lt;/update 2015-01-13&gt;</span>
  </p>

<p>I hope this helps. Any questions please email
<a href= "mailto: bartonphillips@gmail.com">me</a>.</p>
<hr>
$footer
EOF;
