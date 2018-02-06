# Bartonphillips Examples

1. **fancy-button.html** Demonstrates the use of a custome element.

1. **fancy.php** Demonstrates 'import'ing a module 'fancy-tabs.html' which creates a custom
element &lt;fancy-tabs&gt;. The 'fancy-tabs' has four tabs the first one has a 'div'
that is editable. The contents can be 'Submited' via a AJAX that uses 'fancy-tabs.ajax.php'.

1. **import.php** Demonstrates the use of an 'import' module with a 'template' that creates a
'shadow' element. **import.php** uses **import-file2.html**.

1. **shownobots.php** This program does a database query of my 'tracker' table and display all
rows that are not robots or zeros for the given site name.

1. **serialize-unserialize.php** This is a PHP program that does some 'serialize', 'unserialize'
stuff with different object/classes.

1. **weewx-test2.php** displays my weather station. The reload every 5 minutes is done via a websocket
server at **../node-watch/weewx.watch2.js** (see the README.md in that directory). 

   We set `$_site->headFile = 'head.php'` and the other two '...File' to *null* instead
of what is in the mysitemap.json file.

   The css is set to:

   ```
body, html {width: 100%; height: 100%; margin: 0; padding: 0}
/* NOTE and iframe is default 'inline' */
iframe {display: block; width: 100%; height: 100%; border: none;}
   ```

   This makes the *iframe* fill the whole viewport without a border. The *block* display is instead of the
normal *inline* for an *iframe*.

   We use a javascript function in the *iframe* (stopLoad()) to stop the normal reloading of the file
**index.php** in the *weewx* directory.

   ```js
  $('iframe').attr("src", "https://www.bartonphillips.com/weewx/");
  $('iframe').on('load', function() {
     const frame = document.querySelector('iframe');
     frame.contentWindow.stopLoad();
  });
   ```

   To use the *contentWindow* the *frame* must be native javascript not jQuery (no idea why). Anyway,
the `frame.contentWindow.stopLoad();` is actually in the **index.php** file in the *weewx*
directory.

   That is about it for **weewx-test2.php**. See the README.md in the *../node-watch* directory.

# Contact me at [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
