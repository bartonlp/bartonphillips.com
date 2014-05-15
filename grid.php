<?php
// BLP 2014-05-13 -- http://updates.html5rocks.com/2014/03/Get-on-the-CSS-Grid
?>
<!DOCTYPE HTML>
<html>
<head>
<style>
html, body {
  height: 100%;
}

.wrapper {
  padding: 10px;
  display: -moz-grid;
  display: grid;
  grid-template-columns: 200px 1% 1fr 1% 100px;
  grid-template-rows: auto 1% 1fr 1% auto;
}

.content {
  background: beige;
  padding: 0;
}

.header {
  background: tomato;
  grid-column: 1 / span 5;
  grid-row: 1;
}

.footer {
  background: deepskyblue;
  grid-row: 5;
  grid-column: 1;
  grid-column: 1 / span 5;
}

.main {
  background: orangered;
  grid-column: 3;
  grid-row: 3;
}

.sidebar {
  background: lightgreen;
  grid-column: 1;
  grid-row: 3;
}

.ads {
  background: gold;
  grid-column: 5;
  grid-row: 3;
}

h2 {
  margin-top: 0;
}

img {
  display: block;
  max-width: 100%;
  width: auto;
  margin: 0 auto 10px;
}

div {
  color: white;
  padding: 10px;
}

p {
  margin: 0 0 1em 0;
}
</style>
</head>
<body>
<div class="wrapper">
<div class="sidebar"><h2>Sidebar</h2></div>

 <div class="main">
   <h2>Main</h2>
    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>  
    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>  
    <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>  
</div>
   
<div class="footer"><h2>Footer</h2></div>

<div class="header"><h2>Header</h2></div>

<div class="ads"><h2>Ads</h2></div>
</div>
</body>
</html>