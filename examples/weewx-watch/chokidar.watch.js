// This uses chokidar:
// https://github.com/paulmillr/chokidar
// This seems like a pretty good 'watch'. There are a lot of 'on'
// events to check on.

const watch = require('chokidar').watch;

const log = console.log.bind(console);

watch("./test.*", {persistent: true})
.on('change', function(path) {
  log('File', path, 'has been changed');
  if(path == 'test.yy') watcher.close();
})
.on('unlink', function(path) { log('File', path, 'has been removed'); });

require('chokidar').watch('test.txt', {ignored: /[\/\\]\./}).on('all', function(event, path) {
  console.log(`ALL: ${event}, ${path}`);
});
