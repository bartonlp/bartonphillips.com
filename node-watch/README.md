# node-watch

1. **weewx.watch2.js**. This directory has the websocket server. The main program is
**../examples/weewx-test2.php**,
it makes a connection with the server. The main program creates an *iframe* and places
**../weewx/index.php** in it. We use a function in that program to disable the loading of the
page every 5 minutes. 

   Instead the server (**weewx.watch2.js**) creates a *watch* on the **../weewx/index.php**
file and when it changes it pushes a message to the client (**weewx-test2.php**). The client listens
for a *websocket* message from the server and then reloads **index.php**.

   This server uses **https** and the keys are in the **ssl** directory. These are the same keys that
*apache* uses.

1. **chokidar.watch.js** uses the *chokidar* package which seems to work pretty well.

## Contact me: [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
