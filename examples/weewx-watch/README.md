# node-watch

1. **weewx.watch.js**. This directory has the websocket server. The main program is in the 
*examples* directory, **../weewx-test.php**,
it makes a connection with the server. The main program creates an *iframe* and places
**/var/www/bartonphillips.com/weewx/index.php** in it. We use a function in that program to disable the loading of the
page every 5 minutes. 

   Instead the server (**weewx.watch.js**) creates a *watch* on the
**/var/www/bartonphillips.com/weewx/index.php**
file and when it changes it pushes a message to the client (**weewx-test.php**). The client listens
for a *websocket* message from the server and then reloads **index.php**. My *Weather Station* runs
on my home Raspberry Pi and *rsyncs* the files to  **https://www.bartonphillips.com/weewx/**.

   **weewx.watch.js** is a *https* server and a *websocket* server. The *https* keys
are in the **ssl** directory and are the same keys that *apache* uses.

1. **chokidar.watch.js** uses the *chokidar* package which seems to work pretty well.

## Contact me: [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
