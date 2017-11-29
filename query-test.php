<?php
// This is a little experment with the new 'async' and 'await' plus a 'promise'.
// This uses query.ajax.php to get the sql

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->script =<<<EOF
<script>
// pure javascript ajax POST

function ajax(url, params, cb) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
     cb(this.responseText);
    }
  };
  xhttp.open("POST", url, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send(params);
}

// request returns a promise for the ajax call.

function request(url, params) {
	return new Promise(function(resolve, reject){
		// the `ajax(..)` callback should be our
		// promise's `resolve(..)` function
		ajax(url, params, resolve);
	});
}

// This actually returns the promise from request.

function query(sql) {
  return request("query.ajax.php", "sql=" + sql);
}

// Here is the 'async' sql function. It calls 'query' with a await which returns the promise.

async function sql(sql) {
  try {
    var rows = await query(sql);
    r = JSON.parse(rows);
    return r;
  } catch(err) {
    console.log(err);
  }
}

// Everything is displayed via console.log so there is NO browser output.

var test = sql("select id, site, page, agent from barton.tracker where lasttime>current_date() limit 3");

var ret = '';

test.then(function(row) {
  // row is an array of objects {key: value, ...}
  // I use Object.keys() and Object.values() to get the pieces from the object.
  // I will use the first row to get the header info.

  var hdr = Object.keys(row[0]); // an array of keys that we can interate over

  ret = "<table border='1'><thead><tr>";

  for(var h of hdr) {
    ret += '<th>'+h+'</th>';
  }

  ret += "</thead><tbody>";

  // Now get each row from the array

  for(var r of row) {
    ret += "<tr>";

    // Now use values() to get each value of {key: value, ...}

    for(var v of Object.values(r)) {
      ret += '<td>'+v+'</td>';
    }
    ret += "</tr>";
  }
  ret += "</tbody></table>";
  // Now I can use jQuery to place the table.

  $("#tablediv").html(ret);
}).catch(function(err) {
  console.log("error", err);
});

</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<div id='tablediv'></div>
$footer
EOF;
