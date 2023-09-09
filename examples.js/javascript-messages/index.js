// This is used by indexMain.html and sub.html
// The javascript is used by both programs.

let newWindow;

const response = document.getElementById('response'); // indexMain.html and sub.html both have a 'response' item.

window.addEventListener('message', (event) => {
  // foo is a message sent by indexMain.html to sub
  if(event.data?.foo) { // The ?. is a null safe construct. Instead of event.data.foo which could fail.
    response.innerText = event.data.foo;
  }
  // msg is a message sent from sub to indexMain.html
  if(event.data?.msg) {
    response.innerText = event.data.msg;
  }
})

// Used by indexMain.html to send the message to sub.html

const sendMessage = () => {
  newWindow.postMessage({foo: 'bar'}, '*');
}

// Used by indexMain.html to open the popup sub.html

const openNewWindow = () => {
  const params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300`;
  newWindow = window.open('sub.html', 'sub', params);
}

// Used by sub.html to close itself down.

const closeWindow = (message) => {
  window.opener.postMessage({msg: message}, '*');
  window.close();
}

// Used by sub.html to know if it was opened as a popup by
// indexMain.html

if(window.opener) {
  console.log("we have an opener");
  const aspopup = document.querySelectorAll(".aspopup");
  aspopup.forEach((p) => {
    p.style.display = "block";
  });
  document.querySelector("#notAPopup").style.display = "none";
} else {
  console.log("we do NOT have an opener");
  const aspopup = document.querySelectorAll(".aspopup");
  aspopup.forEach((p) => {
    p.style.display = "none";
  });
  document.querySelector("#notAPopup").style.display = "block";
}

