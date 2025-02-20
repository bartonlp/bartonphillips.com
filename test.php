
<?php
$output =<<<'JS'
<script>         
const div = document.querySelector('#div');
const but = document.querySelector("#but");
but.addEventListener("click", function(e) {
  const one = 1;
  const str = "This is a string";

  const output = `<pre>${one}
${str}
Just plain text.</pre>`;

  div.innerHTML = output;
});
</script>
JS;

echo <<<EOF
<!DOCTYPE html>
<html>
<body>
<button id="but">Something New</button>
<div id="div"></div>
$output
</body>
</html>
EOF;
