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
