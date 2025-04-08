<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$S->title = "Errors with Dignity";
$S->banner = "<h1>The Dignity of Error Messages</h1>";

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<h3>🗝 Introduction: A Moment of Friction, or a Moment of Trust?</h3>
<p>
Every piece of software eventually fails. What separates good software from great software isn’t just how it works—but how it <strong>fails</strong>.
</p>
<p>
Far too often, users are met with cold, cryptic, or meaningless error messages:
</p>
<ul>
  <li><em>"Error – OK"</em></li>
  <li><em>"Something went wrong"</em></li>
  <li><em>"Retry"</em></li>
  <li><em>"Error in message stream (Retry)"</em></li>
</ul>
<p>
These messages do nothing to inform, comfort, or empower the user. They are, in essence, <strong>a broken contract between developer and user.</strong>
</p>

<hr>

<h3>💡 The Problem: Errors Without Empathy</h3>
<p>
When a system fails and all a user sees is <em>"Error in message stream (Retry),"</em> it's a double failure:
</p>
<ol>
  <li><strong>The technical failure itself</strong></li>
  <li><strong>The failure to acknowledge the user's experience</strong></li>
</ol>
<p>
Worse still, the lack of actionable information—no code, no guidance, no point of contact—sends the message:  
<em>"We don't really care what went wrong, and we don't expect to fix it."</em>
</p>

<hr>

<h3>✅ The Better Way: Messages with Dignity</h3>
<p>What should a good error message do?</p>
<ul>
  <li><strong>Acknowledge the issue</strong>: <em>“Oops, something didn’t work.”</em></li>
  <li><strong>Provide a clear ID or code</strong> for tracking/debugging.</li>
  <li><strong>Offer next steps</strong>: Retry? Contact? Report?</li>
  <li><strong>Include a human tone</strong>: Apologize. Empathize. Show respect.</li>
  <li><strong>(Optionally) Confirm it's been logged</strong>: Let the user know they’ve been heard.</li>
</ul>

<blockquote>
<p>
<em>"We're sorry—an unexpected error occurred while connecting to our service (Error #7777).<br>
This error has been logged, and one of our engineers will be notified.<br>
Please try again in a few minutes, or <a href='#'>contact support</a>."</em>
</p>
</blockquote>

<p>That one message rebuilds trust, shows accountability, and gives users a path forward.</p>

<hr>

<h3>🔧 The Excuse: “But That Costs Money”</h3>
<p>Yes, thoughtful error handling takes:</p>
<ul>
  <li>More time</li>
  <li>Real engineering effort</li>
  <li>Human support infrastructure</li>
</ul>
<p>But it also:</p>
<ul>
  <li>Saves time in the long run (fewer bug reports, clearer diagnostics)</li>
  <li>Builds user trust and loyalty</li>
  <li>Differentiates good software from bad</li>
</ul>
<p>
This isn’t just a matter of making the user experience more pleasant—it’s about treating people with respect, especially when things go wrong. It’s part of <strong>software ethics</strong>.
</p>

<hr>

<h3>✨ Final Thought</h3>
<p>
We’ve all been on the receiving end of software that fails without grace. But when a developer takes the time to write a clear, honest, and human-centered error message, something subtle but powerful happens:
</p>
<blockquote>
<p><em>The user feels seen.</em></p>
</blockquote>
<p>
That’s what <em>The Dignity of Error Messages</em> is all about.
</p>
<hr>
$bottom
EOF;
