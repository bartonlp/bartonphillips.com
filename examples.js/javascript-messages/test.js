// test.js
// This is a module that is used by popup-onlyjs2.php

function test() {
  site = `<body>
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
  return site;
}

export default test();


