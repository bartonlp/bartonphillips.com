<?php
// How to load LinuxMint 15 from iso
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp; // takes an array if you want to change defaults

$h->title = "Load LinuxMint 15 from ISO";
$h->banner = "<h1 class='center'>How to Load LinuxMint 15 via ISO from Disk</h1>";

list($top, $footer) = $S->getPageTopBottom($h);
echo <<<EOF
$top

<p>Instead of burning a CD or using a USB flash stick you can install Linux Mint 15 (and most other
Linux distributions) from a hard disk. All you need is a small (about 2 GB) extra partition
somewhere.  This example is for 'Linux Mint 15 Mate 64'. The arguments you would use for other
distribution might (and probably will be) different.</p>

<p>This method uses GRUB2 but I believe GRUB will also work with different syntax (not shown here).</p>

<p>You should edit the GRUB2 configruation rather than editing '/boot/grub/grub.cfg'. The
configuration files are at '/etc/grub.d/' and the file you want to edit is '40_custom'.</p>

<p>There will probably be nothing in that file to begin with other than the first five lines below.
Add the following lines after the header that was there:</p>

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

<p>The first five lines are what is usually in the file to start. Add the other five lines and then
edit them to reflect your environment. You will need to edit the lines that start with
'loopback' and 'linux'. On my system I have a seperate partition for my '/home' directory.
This makes it much easier when it comes time to upgrade as my 'home' directory is safe and I can
also put my ISO somewhere on that partition.</p>

<pre>
(hd1,msdos5)/barton/Downloads/linuxmint-15-mate-dvd-64bit.iso
</pre>

<p><i>(hd1,msdos5)</i> means the second hard disk in my system (I have two internal hard drives one
with 320 GB and one with 500 GB). <i>hd1</i> is the 500 GB drive which has five partitions. My 'home'
directory is on partition five (msdos5). In the '/home' directory is my home '/home/barton/' and the
'Linux Mint ISO' is in the 'Downloads' directory.</p>

<p>The ISO is only about one GB so you really don't need a very big partition. Unfortunatly you do
need a partition other than the one where you will install the new OS. If you don't have a seperate
partition for '/home' you can use 'parted' (sudo apt-get install parted) or some other utility to
create a little partition, 2 GB is plenty.</p>

<p>The next line which starts with 'linux' only needs the section
<i>iso-scan/filename=/barton/Downloads/linuxmint-15-mate-dvd-64bit.iso</i> changed to match where your
ISO is, which is the same as the line above without the <i>(h1,msdos5)</i> part.</p>
<p>Once you have the new code added to the '40_custom' file you need to update the 'grub.cfg' which is
probably in your '/boot/grub' directory. Run 'sudo grub-mkconfig --output=filename' or you can just
do 'sudo grub-mkconfig >filename' as without the option the output goes to stdout. I would create a
file somewhere other than '/boot/grub' so I could look at the output first and make sure it is OK.</p>

<p>Once you think the output of OK you can move it to the location of 'grub.cfg' (usually at
/boot/grub). You might want to make a backup of the original file (or not, up to you).</p>

<p>Now it is time to reboot your system. Oh, you might want to backup anything you don't want to lose.
Some things to think about backing up are things in '/etc' that you have customized, your '/var/www'
if you have one, and anything else that is on the partition where the new OS is going that you don't
want to lose. If you '/home' directory is on that partition you should probably back it up also.</p>

<p>When you reboot select the new 'Linux Mint 15 Mate ISO' entry which brings up the 'Live CD'
from which you can install the new OS.</p>

<p>BUT, maybe it doesn't work. You can use the GRUB edit facilities and command line to look to see
if you have the drive and partition stuff correct. Using the 'c' option on the first GRUB menue you
can look at the drive where you think your ISO is. Type 'ls (h1,msdos5)', for example, and you should
see the files and directories. If you type the full path to your ISO you should see it. If not then
you got the drive and partition wrong. Use the 'ls' command and find your ISO using other disk and
partition values. Remember the 'hd' starts at zero not one, so the first disk drive is 'hd0'.
Partitions start at one not zero, so '(hd1,msdos1) means the second disk drive on the system
and the first parition ('consistency is the hobgoblin of little minds').</p>

<p>If you still can't get the boot to work reboot your old OS and review everything and maybe do some
'google' searches. This all worked for me for the last three Linux Mint releases and two Ubuntu
releases before that. Note that some the the arguments have changed on different releases. These
arguments work for 'Linux Mint 15 Mate 64'.</p>

<p>I hope this helps. Any questions please email
<a href= "mailto: bartonphillips@gmail.com">me</a>.</p>
<hr>
$footer
EOF;
?>
