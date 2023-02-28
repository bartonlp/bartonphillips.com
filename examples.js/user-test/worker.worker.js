// worker.worker.js This is javascript.
// This is the worker side of worker.main.php and it calls
// worker.ajax.php for the info from the 'test' table.
// See worker.ajax.php for description of the 'test' table in database
// 'test'.

// Add an event listener for 'message'. The data is in evt.data and we
// make it into a string and then pass the string to sendText()

addEventListener("message", function(evt) {
  var string = new TextDecoder("utf-8").decode(evt.data);
  console.log("Worker string: ", string);
  sendText(string);
});

// SendText() does the usual XMLHttpRequest() stuff to post to
// worker.ajax.php.

function sendText(txt) {
  // Use fetch() to send and receive the data.
  
  let ret = fetch("worker.ajax.php", {
    body: txt, // This is just plain sql
    method: "POST",
    headers: {
      'content-type': 'application/x-www-form-urlencoded'
    }
  }).then(res => res.json()); // Get the json data
  ret.then(newtxt => {
    console.log("Worker response", newtxt);

    if(Object.keys(newtxt) == "ERROR" || Object.keys(newtxt) == "DONE") {
      postMessage(newtxt);
    } else {
      // Take the items out of newtxt which is an array.

      var rows = '';

      for(item of newtxt) {
        // Now the stuff in the array is an object so get the key and
        // value and put them into the rows variable.

        for([key, value] of Object.entries(item)) {
          rows += key + ": " + value + "\n";
        }
        rows += "\n";
      }

      // Now we do the same thing we did above to make the Transfer
      // buffer

      bufView = Uint8Array.from(rows, x => x.charCodeAt());
      console.log("Worker bufView: ", bufView);
      postMessage(bufView, [bufView.buffer]);
    }
  });
};
