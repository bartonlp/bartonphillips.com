// Feature Test
// Uses Modernizr to detect features. 
// This used the stuff from featuretest.js which must have already been
// loaded.

function modernizrloaded() {
  // featuretest.js does a getScript() for modernizr.js so we must wait
  // till the asyncronous load is done.
  
  if(typeof Modernizr == "undefined") {
    setTimeout(modernizrloaded, 0);
  } else {
    $('#supported').html(ok.join("<br>\n"));
    $('#notsupported').html(notok.join("<br>\n"));
  }
}

var ok=new Array;

jQuery(window).load(function() {
  $= jQuery;
  
  for(var v in cl) {
    if(cl[v]) {
      if(!/^no-/.test(cl[v])) {
        ok.push(cl[v]);
      }
    }
  }

  if(Modernizr.audio) {
    var x;
    if((x=Modernizr.audio.ogg)) {
      ok.push("audio.ogg="+x);
    }
    if((x=Modernizr.audio.mp3)) {
      ok.push("audio.mp3="+x);
    }
    if((x=Modernizr.audio.wav)) {
      ok.push("audio.wav="+x);
    }
    if((x=Modernizr.audio.m4a)) {
      ok.push("audio.m4a="+x);
    }
  }
  if(Modernizr.video) {
    var x;
    if((x=Modernizr.video.ogg)) {
      ok.push("video.ogg="+x);
    }
    if((x=Modernizr.video.webm)) {
      ok.push("video.webm="+x);
    }
    if((x=Modernizr.video.h264)) {
      ok.push("video.h264="+x);
    }
  }

  var URL = window.URL || window.webkitURL; // || window.oURL;
  
  if(Object.toType(URL) === 'undefined') {
    //alert("No URL");
    notok.push("no-URL-object");
  } else {
    ok.push("URL-object");
    if(Object.toType(URL.createObjectURL) !== "function") {
      //alert("No createObjectURL()");
      notok.push("no-url-createObjectURL");
    } else {
      ok.push("url-createObjectURL");
    }
  }
  setTimeout(modernizrloaded, 0);
});





  

  