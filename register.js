// BLP 2023-08-13 - not used -- I think.
// This code is inline in register.php

'use strict';

const ajaxFile = "register.php";

console.log(ajaxFile);
console.log("lastId: "+lastId);
//debugger; // BLP 2021-12-29 -- Force a breakpoint here

// Get the visitor identifier (fingerprint) when you need it.

$("#submit").on("click", function() {
  //debugger;
  let email = $("#email").val();
  let name = $("#name").val();
  
  fpPromise
  .then(fp => fp.get())
  .then(result => {
    // This is the visitor identifier:
    const visitorId = result.visitorId;

    console.log("visitor: " + visitorId);

    $.ajax({
      url: ajaxFile,
      data: { page: 'finger', visitor: visitorId, email: email, name: name },
      type: 'post',
      success: function(data) {
        console.log("return: " + data);
        $("#container").html("<h1>Registration Complete</h1><a href='/" + data + "'>Return to Home Page</a>");
      },
      error: function(err) {
        console.log(err);
      }
    });
  });
});
