/* Straight Javascript */

document.head.innerHTML = "<title>JavaScript Only</title>"+
                          "<meta name=viewport content='width=device-width, initial-scale=1'>"+
                          "<meta charset='utf-8'>"+
                          "<meta name='copyright' content='2016 Barton L. Phillips'>"+
                          "<meta name='Author' content='Barton L. Phillips, mailto:bartonphillips@gmail.com'>"+
                          "<meta name='description' content='Bartonphillips'>"+
                          "<link rel='stylesheet' href='https://bartonphillips.net/css/blp.css'>"+
                          "<style>"+
                          "pre {"+
                          "  font-size: .7em;"+
                          "  overflow: auto;"+
                          "  padding: .5em;"+
                          "  border-left: .5em solid gray;"+
                          "  background-color: #E5E5E5;"+
                          "}"+
                          "</style>";

document.body.innerHTML = "<h1><i>javascipt</i> Only no <i>jQuery</i> or <i>SiteClass</i></h1>"+
                          "<h3>This program is straight <i>HTML</i> and <i>javascript</i> and does not have a fancy header or footer.</h3>" +
                          "<p>To see the source code you can use the Chrome DevTools.</p>"+
                          "<a href='javascript-siteclass.php'>JavaScript only plus jQuery and SiteClass</a><br>"+
                          "<a href='javascript-only.php'>JavaScript only with jQuery and no SiteClass</a><br><br>"+
                          "<a href='javascript-only-show.php?item=1'>This <i>HTML</i> file.</a><br>"+
                          "<a href='javascript-only-show.php?item=2'>The <i>javascript</i> file.</a>";
