# node.js Examples
## This directory's files use the node_module in this directory.

The **websocket-client.js** can be run on any computer that has a recent version of node.js.
The **websocket-test.php** can be run on any computer browser.

The **websocket-server.js** should be run on a server (like **bartonlp.org**) with the current
version of node.js. Enter 

`node websocket-server.js`

It will listen on port 8080 for client requests from the client programs.

**websocket-client.js** and **websocket-test.php** will send 'hello' to the server which will
echo the message back to the clients.

This was written to replace https://Pusher.com for the *myPhotochannel* program. *myPhotochannel* is a 
slideshow program designed for resterants and bars.

Instead of using *Pusher* this program accepts four events in addition to the 'hello' event from the
two client programs:

1. register. Send by programs that want to receive messages. Either
the siteId is send or ALL. Programs that want to monitor messages to
ALL sites set siteId to ALL.
2. fastcall. This is triggered by actions from the Cpanel. When:
  * a photo is approved
  * the following tables are modified: appinfo, categories, segments, sites, or items.
3. startup. Send when a program starts, like cpanel or slideshow.
4. shutdown. Send to ALL by this program when a connection
terminates.

The [websocket.conf](./websocket.conf) is an example of how to set up **upstart**. This config
file should be placed in the */etc/init/* directory.

This server actually runs *systemd* and I do not have a config file for that yet.

## Contact me: [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)
