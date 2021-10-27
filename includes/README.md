# Includes for the bartonphillips.com domain
The head.i.php and footer.i.php files are used by most of my domains. Some, like bartonlp.org (at www/html), 
use the entire includes directory as a simlink while others just have head.i.php or footer.i.php symlinked.

# Update BLP 2021-10-13 -- 
The head.i.php file now uses $h instead of $arg. The footer.i.php file now uses $b instead of $arg. SiteClass has been changed.
getPageTop($h) now uses only the object $h. getPageHead($h) now only uses object $h. getPageFooter($b) now only uses object $b.

# Update BLP 2021-06-07 -- 
I have changed the way tracker.php is used. Now tracker.php resides with http://bartonphillips.net/tracker.php.  
In head.i.php and banner.i.php I have made changes. Now there is a section in head.i.php that looks to see if the nodb or noTrack flags have been
set in mysitemap.json. If either is true then tracker.js is not loaded. If they are not true then it is loaded from https://bartonphillips.net/js.
I use the data attribute of the script tag to pass the LAST_ID information to tracker.js. The attribute 'data-lastid' is used by tracker.js
to add the 'csstest-{LAST_ID}.css' file just before the script. For this to work the .htaccess file must have a RewriteRule that
redirects the 'csstest-{LAST_ID}.css' to https://bartonphillips.net/tracker.php?id={LAST_ID}&csstest. (see tracker.js and .htaccess)

I have also changed banner.i.php. It also checks the two flags and only if they are both not true is this code included:

      $image2 =<<<EOF
      <a>
        <img src="https://bartonphillips.net/tracker.php?page=normal&id=$this->LAST_ID&image=$this->trackerImg2" alt="linux counter image.">
      </a>
      EOF;
      $image3 = "<img src='https://bartonphillips.net/tracker.php?page=noscript&id=$this->LAST_ID'>";

The $image2 is used to track 'normal' pages. The $image3 goes into the 'noscript' block. The 'logo' image uses data-image attribute of the
img tag with the logo id to set the final image which becomes https://bartonphillips.net/tracker.php?page=script&id={LAST_ID}&image={data-image}.
Replace the items in the {} with the actual LAST_ID value and the value from the data-image attribute. (see tracker.js)

Previously I had to have symlinks in all of the domains and now the tracker.php is located in https://bartonphillips.net and I don't need
all of the symlinks.



