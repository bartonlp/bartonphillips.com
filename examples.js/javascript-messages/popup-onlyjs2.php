<?php
// popup-onlyjs2.php
// This demo creates a popup using only javascript with a document.write().
// You can change between the inline code and the test.js code by
// adding a "?script=true" to get
// the test.js code.

// I use my framework to set up things. Information at https://github.com/bartonlp/site-class
   
$_site = require_once(getenv("SITELOADNAME")); 
$S = new SiteClass($_site);
$S->title = "Demo Using Only Javascript";
$S->banner = "<h1>$S->title</h1>";
$S->css =<<<EOF
#programdata {
  display: none;
  border: 1px solid black;
  padding: 5px;
  #overflow: auto; background: #fffdf8;
}
#response b { cursor: pointer; }
EOF;

if($_POST['page'] == "post") {
  $filename = $_POST['msg'];
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  $file = "<pre class='line-numbers'><code class='language-$ext'>" . escapeltgt(file_get_contents("$filename")) . "</code></pre>";
  echo "This is the contents of $filename: $file<br>";
  exit();
}

$script = $_GET['script']; // If a "?script=true" is added we then use test.js rather than the inline code.

$lines = glob("*.php");
$lines = array_merge($lines, glob("*.html"));

foreach($lines as $item) {
  $items .= "<input type='checkbox' value='$item'>$item<br>";
}

// Get the page source's

$mprog = "This is popup-onlyjs2.php<pre class='line-numbers'><code class='language-php'>" . escapeltgt(file_get_contents("popup-onlyjs2.php")) . "</code></pre>";
$mprog .= "This is test.js<pre class='line-numbers'><code class='language-js'>" . escapeltgt(file_get_contents("test.js")) . "</code></pre>";


// Set up the javascript code at the bottom of the page.

$S->b_script =<<<EOF
<script src='https://bartonphillips.net/js/prism.js'></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/prism.css">

<!-- This script is a module. -->

<script>
  var site = `$items`; // This is how we pass the var to the module
</script>

<script type='module'>
  const script = "$script";
  
  // Create an popup window.

  const params = "left=1000, width=300,height=900";
  const newWindow = window.open('', '', params);

  // Change this true/false depending on which you want to use.

  if(script) {
    // import from ./test.js, then get the default export and then use the function test().

    site = ((await import('./test.js')).default);
    
    $("#script-inline").html("This is using the 'test.js' code");
  } else {
    $("#script-inline").html("This is using the inline code");
    site = `
<body>
<script>
  const body = document.querySelector("body");
  body.innerHTML = "<h1>This is interesting</h1>` + site + `";

  let inputs = document.querySelectorAll("input");
  inputs.forEach((input) => {
    input.addEventListener("click", (event) => {
      let msg = event.target.defaultValue;
      window.opener.postMessage({msg: msg}, '*');
      window.close();
    });
  });
<\/script>
`;
  }

  // Write the popup's information and then close the write.

  newWindow.document.write(site);
  newWindow.document.close();

  // Add an event listener for 'message'. This will receive the information from the popup.
  
  window.addEventListener('message', (event) => {
    if(event.data?.msg) {
      $("#response").html(`You have selected: <b>\${event.data.msg}</b><br>You can click on the name
      above to see the code.`);
      $("#response b").on("click", function() {
        const msg = $("#response b").html();
        $.ajax({
          url:'popup-onlyjs2.php',
          type: 'post',
          data: {page: "post", msg: msg},
          success: function (data) {
            const r = $("#response");
            console.log("r: ", r);
            $(r).html(data).css({"border": "1px solid black",
            "padding": "5px", "overflow": "auto", "background":
            "#fffdf8"});
            Prism.highlightAll();
            //Prism.highlightAllUnder(r[0]);
          },
          error: function(err) {
            console.log("Error: ", err);
          }
        });
      });
    }  
  });
  
  $("#showprogram").on("click", function() {
    $("#programdata").show(); 
    $(this).hide(); // Must use 'function()' not '() => {' because the latter has NO this.
    //Prism.highlightAll();
  });

  $(window).on("close, visibilitychange, beforeunload, pagehide", function() {
    newWindow.window.close();
  });
</script>
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>The popup is created with <i>newWindow = window.open(...)</i> and then the information is written with
<i>newWindow.document.document.write(site)</i>. <i>site</i> is a variable that contains all the information of the popup.</p>
<p id="script-inline"></p>
<p id="response"></p>
<button id="showprogram">Show Sources</button>
<div id="programdata">$mprog</div>
$footer
EOF;
