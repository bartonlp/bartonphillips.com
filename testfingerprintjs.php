<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->noGeo = true;

$S->h_script =<<<EOF
<script src="https://www.google.com/recaptcha/api.js?render=6LefxlMnAAAAALcjQAYEBYCOhBXpLDGEL0Q8NzMt"
        async defer></script>
<script>
  function onLoad() {
    grecaptcha.ready(function() {
      grecaptcha.execute('6LefxlMnAAAAALcjQAYEBYCOhBXpLDGEL0Q8NzMt', {action: 'submit'}).then(function(token) {
        // Add your logic to submit to your backend server here.
        console.log("recapcha: token=", token);
      });
    });
  }
</script>
EOF;

$S->b_script =<<<EOF
<script type="module">
  // Initialize the agent at application startup.
  //const fpPromise = import('https://bartonphillips.net/js/fingerprintjs-min.js')
  const fpPromise = import('https://openfpcdn.io/fingerprintjs/v3')
    .then(FingerprintJS => FingerprintJS.load());

  // Get the visitor identifier when you need it.
  fpPromise
    .then(fp => fp.get())
    .then(result => {
      // This is the visitor identifier:
      const visitorId = result.visitorId;
      console.log(visitorId);
      $("#visitorid").html(visitorId);
    })

    // Initialize an agent at application startup, once per page/app.
    const botdPromise = import('https://openfpcdn.io/botd/v1').then((Botd) => Botd.load())
    //const botdPromise = import('https://bartonphillips.net/js/BotD.js')
    //.then((Botd) => Botd.load());
    // Get detection results when you need them.
    botdPromise
        .then((botd) => botd.detect())
        .then((result) => {
          console.log(result);
          $("#isbot").html(result.bot ? "true" : "false");
          if(result.bot === true) {
            const agent = navigator.userAgent;
            const res = JSON.stringify(result);
            console.log("site: " , thesite, agent, res);
            $.ajax({
              url: "geoAjax.php",
              type: "post",
              data: { page: 'testbotd', site: thesite, ip: theip, agent: agent, result: res },
              success: (data) => {
                console.log("agent is a bot: ", data);
              },
              error: (err) => console.log("ERROR: ", err)
            });
          }  
        })
        .catch((error) => console.log("CATCH ERROR", error))
</script>
EOF;

$S->bodytag = "<body onload=onLoad()>";
[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p id="visitorid">Id Goes Here</p>
<p id="isbot">Are You a Bot</p>
$footer
EOF;
