<?php
// fancy.php. This uses fancy-tab.html as an 'import' 'link'
// it uses 'fancy-tabs.html'.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->css =<<<EOF
<style>
/* We can set global values in the :root section.
 * These values can then be used in other sections.
 * --height: 350px;
 * can be used in another as height: var(--height); or as height: var(--height, 500px); which
 * means, if --height is defined use --height otherwise use 500px.
 */
:root {
  /*--background: pink;*/
  --height: 350px;
}
/* This is the div in the first section */
#panel-1 {
  width: 20rem;
  height: 10rem;
  border: 1px solid black;
  background: white;
  overflow: auto;
}
</style>
EOF;

$h->script =<<<EOF
<script>
function DoIt(e, link) {
  console.log("the file fancy-tabs.html is loaded with a 'rel=\"import\"");
  console.log("e:", e, " link:", link);
}

jQuery(document).ready(function($) {
  function supportsImports() {
    // is 'import' in document.createElement('link') if yes then 'import' is supported.

    return 'import' in document.createElement('link');
  }

  // if 'import's are supported otherwise error.

  if(supportsImports()) {
    // lets create a 'link' element.

    var link = document.createElement('link');

    // set the 'rel' attribute to 'import'

    link.rel = 'import';

    // we will use 'fancy-tabs.html' as the 'module' we want to 'import'

    link.href = 'fancy-tabs.html'; // start the load.

    //link.setAttribute('async', ''); // make it async! OR NOT.

    link.onload = function(e) {DoIt(e, link)}; // DoIt just logs

    link.onerror = function(e) {
      console.log("ERROR: ", e);
    }

    // Add the 'link' at the end of <head>

    document.head.appendChild(link);
  } else {
    alert("No Support for 'import's");
    return;
  }

  // This is the ajax to post the info

  $("input[type='submit']").click(function() {
    var div = $("#panel-1").text();

    $.ajax({
      url: "fancy-tabs.ajax.php",
      data: { text: div },
      type: "post",
      success: function(data) {
        console.log(data);
        $("#panel-1").append(data);
        return false;
      },
      error: (err) => console.log(err)
    });
    return false;
  });  
});
</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>This is a demo of an 'import' of a templet file 'fancy-tabs.html'. We use the 'customElement'
&lt;fancy-tabs&gt; to make the nice tabbed display.</p>

<fancy-tabs background>
  <button slot="title">Tab 1</button>
  <button slot="title">Tab 2</button>
  <button slot="title">Tab 3</button>
  <button slot="title">Tab 4</button>
  <section>content panel 1<br>
    <form method='post' action="input.ajax.php">
      <div id='panel-1' contenteditable>Test it out</div>
      <input type='submit' value="Submit">
    </form>
  </section>
  <section>content panel 2</section>
  <section>content panel 3</section>
  <section>content panel 4</section>
</fancy-tabs>

$footer
EOF;

