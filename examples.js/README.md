# Java Script Examples

These files have experments and examples of various Java Script technologies.

In the directory *seviceworker* are examples of running a service worker for an HTML program.
*index.html* is from Gooble and uses *service-worker.js*. It shows the prefetch and caching
of files for offline use.

In the directory *user-test* are programs to demonstrate use of **worker.worker.js** which works
with **worker.ajax.php** to show message passing between the main program, **worker.main.php**,
and the worker. The main program lets the user enter *sql* statements which are passed the the
worker and via *ajax* to the **worker.ajax.php** which returns the *sql* results which is then passed
back to the main program via *postMessage()*. There is only one table that can be used and it is
limited to 20 rows at which time the oldest rows are deleted.

The rest of the files in *examples.js* demonstrate other examples of Java Script technology.

## Contact me: [bartonphillips@gmail.com](mailto:bartonphillips@gmail.com)

