// BLP 2022-01-16 -- This is used by bonnieburch.com/addcookie.php
// It can be used by other files if needed. We pass page=finger and
// visitor=visitorId via AJAX to the ajaxFile (which is the php file
// that called this).

'use strict';

const ajaxFile = "register.php";

console.log(ajaxFile);
console.log("lastId: "+lastId);
//debugger; // BLP 2021-12-29 -- Force a breakpoint here

// Get the visitor identifier (fingerprint) when you need it.

$("#submit").on("click", function() {
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
        $("#container").html(data);
      },
      error: function(err) {
        console.log(err);
      }
    });
  });
});
