<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->css =<<<EOF
<style>
:root {
  /*--background: pink;*/
  --height: 350px;
}
/*fancy-tabs {
  margin-left: 10rem;
  margin-bottom: 32px;
  --background-color: black;
}*/
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
  console.log("e:", e);
}

jQuery(document).ready(function($) {
  function supportsImports() {
    return 'import' in document.createElement('link');
  }

  if(supportsImports()) {
    var link = document.createElement('link');
    link.rel = 'import';
    link.href = 'fancy-tabs.html';
    //link.setAttribute('async', ''); // make it async!
    link.onload = function(e) {DoIt(e, link)};
    link.onerror = function(e) {
      console.log("ERROR: ", e);
    }

    document.head.appendChild(link);
  } else {
    alert("No Support for 'imports'");
    return;
  }

  // This is the ajax to post the info

  $("input[type='submit']").click(function() {
    var div = $("#panel-1").text();

    $.ajax({
      url: "input.ajax.php",
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

