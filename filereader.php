<?php
// BLP 2014-05-12 -- FireReader example: http://www.html5rocks.com/en/tutorials/file/dndfiles/
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

$h->extra = <<<EOF
<script>
jQuery(document).ready(function($) {
  // Check for the various File API support.
  if(!(window.File && window.FileReader && window.FileList && window.Blob)) {
    alert('The File APIs are not fully supported in this browser.');
  }

  function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object

    // files is a FileList of File objects. List some properties.
    var output = [];
    for(var i = 0, f; f = files[i]; i++) {
      output.push('<li><strong>', escape(f.name), '</strong> (', f.type || 'n/a', ') - ',
                  f.size, ' bytes, last modified: ',
                  f.lastModifiedDate ? f.lastModifiedDate.toLocaleDateString() : 'n/a',
                  '</li>');
    }
    $("#list").html('<ul>' + output.join('') + '</ul>');
  }

  $('#files').change(handleFileSelect);
});
</script>
EOF;

$h->title = "Filereader Interface";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>Find the full document at <a href="http://www.html5rocks.com/en/tutorials/file/dndfiles/">
 http://www.html5rocks.com/en/tutorials/file/dndfiles/</a></p>
<p>In each example you can also drag files to the 'choose' button and drop them.</p>

<h3>File Info</h3>
<p>Select files and see the 'name', 'filetype', 'filesize' and 'last modified'</p>
<input type="file" id="files" name="files[]" multiple />
<output id="list"></output>
<h3>Select Image files</h3>
<p>Select some image file and they will be displayed as thumbnails</p>
<style>
  .thumb {
    height: 75px;
    border: 1px solid #000;
    margin: 10px 5px 0 0;
  }
</style>

<input type="file" id="files2" name="files[]" multiple />
<output id="list2"></output>

<script>
  function handleFileSelect2(evt) {
    var files = evt.target.files; // FileList object

    // Loop through the FileList and render image files as thumbnails.
    for(var i = 0, f; f = files[i]; i++) {
      // Only process image files.
      if(!f.type.match('image.*')) {
        continue;
      }

      var reader = new FileReader();

      // Closure to capture the file information.
      reader.onload = (function(theFile) {
        return function(e) {
          // Render thumbnail.
          var span = document.createElement('span');
          span.innerHTML = ['<img class="thumb" src="', e.target.result,
                            '" title="', escape(theFile.name), '"/>'].join('');
          document.getElementById('list2').insertBefore(span, null);
        };
      })(f);

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
  }

  document.getElementById('files2').addEventListener('change', handleFileSelect2, false);
</script>

<h3>Select a text file and slice it up</h3>
<p>Select a text file and then choose the slice you want to view</p>

<style>
  #byte_content {
    margin: 5px 0;
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
  }
  #byte_range { margin-top: 5px; }
</style>

<input type="file" id="files3" name="file" /> Read bytes: 
<span class="readBytesButtons">
  <button data-startbyte="0" data-endbyte="4">1-5</button>
  <button data-startbyte="5" data-endbyte="14">6-15</button>
  <button data-startbyte="6" data-endbyte="7">7-8</button>
  <button>entire file</button>
</span>
<div id="byte_range"></div>
<div id="byte_content"></div>

<script>
  function readBlob(opt_startByte, opt_stopByte) {

    var files = document.getElementById('files3').files;
    if (!files.length) {
      alert('Please select a file!');
      return;
    }

    var file = files[0];
    var start = parseInt(opt_startByte) || 0;
    var stop = parseInt(opt_stopByte) || file.size - 1;

    var reader = new FileReader();

    // If we use onloadend, we need to check the readyState.
    reader.onloadend = function(evt) {
      if (evt.target.readyState == FileReader.DONE) { // DONE == 2
        document.getElementById('byte_content').textContent = evt.target.result;
        document.getElementById('byte_range').textContent = 
            ['Read bytes: ', start + 1, ' - ', stop + 1,
             ' of ', file.size, ' byte file'].join('');
      }
    };

    var blob = file.slice(start, stop + 1);
    reader.readAsBinaryString(blob);
  }
  
  document.querySelector('.readBytesButtons').addEventListener('click', function(evt) {
    if (evt.target.tagName.toLowerCase() == 'button') {
      var startByte = evt.target.getAttribute('data-startbyte');
      var endByte = evt.target.getAttribute('data-endbyte');
      readBlob(startByte, endByte);
    }
  }, false);
</script>  
<hr>  
$footer
EOF;
