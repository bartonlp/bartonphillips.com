<?php
require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp; // takes an array if you want to change defaults

$h->title = "How the Internet Works";
$h->banner = "<h1 class='center'>How the Internet Works</h1><hr>";
$h->extra = <<<EOF
<style>
pre {
  border-left: 5px solid #ccc;
  margin-left: 4px;
  padding-left: 10px;
}
</style>

EOF;

list($top, $footer) = $S->getPageTopBottom($h);
echo $top;
?>

<h2 id="basics">Basic Principal Simplified</h2>

<p>We already know how something kinda like the Internet works: the Post Office. The way we send
  and receive mail is a good analog to the way the Internet works.</p>

<p>When we want to send a leter to someone we write the letter and place it in an envelope. The
  envelope has the recipient's address and our return address. It the correspondence is conducted over
  a period of time, that is we send several pieces of mail, then we place some form of sequencing
  information inside the envelope, like a date or a chapter number etc.</p>

<p>We then put the letter in the mail box. At some point the mailman comes and collects the letter
  and drives to the local post office. Once it gets to the local post office the mail is sorted. In
  the old days this was all done by people. The mail was divided into manageable batches and post
  office employees "threw" the mail into bins with little cubby holes marked with the names of
  states or locales. Local mail was placed in a separate bin and was further sorted by local
  addresses. After the mail was sorted into states or locales it was taken to a regional post office,
  an office that handled a number of local post offices. There is was further sorted and routed to
  various transport systems. For example mail that was going to locations within the state where the
  local post office was might be placed on trucks or trains. Mail that was going a long distance was
  placed on airplanes that took the mail to a major post office in the state of reigen where the
  recipient lived.</p>

<p>The delivery process from the major post office nearest the recipient was basically the reverse
  of the process from the sender.</p>

<p>Even though the mail traveled via several transport mechanisms it was at the routing end points
  that the letters envelope was examined to see where it would next travel.</p>

<p>The envelop is analogous to the TCP/IP IP header which contains the senders IP address and the
destination IP address. The trucks, trains, and planes are analogous to the various electronic,
optical fiber or radio transport mechanisms, like Ethernet local area networks, T-1 local ISP, OC-3
and faster optical fiber transmission lines, radio and satellites. Depending of how fare away the
destination address is different transmission media are used. The transmission media has no
knowledge of the sender or receiver address. All of that information (and more) is in packets in the
transmission media just like the letter inside of a mail truck, or train or plane. Only at the
transmission end points is the information unpacked and routed further along its path.</p>

<p>The end point routers have information that lets them determine what transmission route to take
to get to the final destination. This process continues on within the &quot;cloud&quot; to the local
network to the clients computer and into the destination &quot;INBOX&quot; (in the case of E-mail).
While the Internet seems more mysterious and complicated the basic principles of delivery are very
similar to the good old post office.</p>

<h2 id="details">More Details</h2>

<p>The TCP/IP stack, as it is called, is made up of several layers. The top layer is the
<i>application</i> layer with protocals like SMTP, HTTP, FTP,etc. Next is the <i>transport</i> layer
-- UDP or TCP. This layer handles the sequencing and end point assignments (<a
href="#ports">ports</a>). A port is a computer location where a specific piece of software listens
for a specific protocal. For example the HTTP protocal is usually associated with port 80. The next
layer is <i>Internet</i> layer where the &quot;envelope&quot; is addressed with the sender's IP
address and the recipient's IP address.  The final layer is the the <i>link</i> layer where the
routing is done.</p>

<p>The TCP/IP stack does not concern itself with the final layer which is the <i>physical</i> layer.
This is the part that does the actual transmission of the data via the transmission media. That
media can be copper wire, optical fiber, radio or satellite.</p>

<p>At every layer of the TCP/IP stack the users data is encapsulated in identifying headers and
  processed by specific software that understands that type of data. At the application layer there
  are many different protocols and software but as we decent the stack the choices narrow.</p>

<p>At the <i>transport</i> layer there are primarily only UDP and TCP. UDP (User Datagram Protocol)
is a simpler point to point connectionless protocol, that is once the message is sent there is no
handshake that guarantees that the message has been received (connectionless). TCP is a point to
point connection based protocol. When a TCP message is transmitted a connection between the sender
and the receiver is set up and the receiver tells the sender that it has received the message
correctly.  If there is a problem the sender resends the information until it is received OK (or the
connection times out in which an error is sent back to the initiating process.)</p>

<p>UDP is a faster protocal and is used where errors are less important than speed. Things like
  voice over IP or on demand video where real time is the key concern use UDP or UDP type
  protocals.</p>

<p>At the <i>Internet</i> layer there are two primary choices for user data packets: IPv4 or IPv6.
The original Internet was based on IPv4 (IP version 4) which provides a 32 bit address
(4.29x10<sup>9</sup>, about 4 billion separate IP addresses). This seemed like an enormous number of
addresses back in 1974 when the Internet Protocol was fist developed. However, it soon became
evident that with the Internet's exponential growth this seemingly enormous number of addresses was
not going to be even close to enough. IPv6 (Internet Protocol version 6) was proposed in the late
1980's. IPv6 uses a 64 bit IP address which yields 1.844674407x10<sup>19</sup> addresses, somewhat
larger, in fact over a billion times larger, probably enough IP addresses for a couple of more
years.</p>

<p>There have been some substantial hurdles in transitioning from IPv4 to IPv6 and it is only
  recently that IPv6 has been deployed by more than a few ISP and upper tier providers. However with
  the exhaustion of the IPv4 addresses implementation has become essential.</p>

<p>At the <i>link</i> layer there are several routing protocols (ARP/InARP, NDP etc.). These
protocols are used by the routers at end points.</p>

<p>At the very bottom is the physical hardware and transport media. Finally the data is turned into
electronic or optical signals by the low level hardware like Ethernet, SONET, etc. Even at this
layer the process has several levels. A final header is applied that contains a MAC (Media Access
Control) address which is a <i>physical</i> layer unique address. Every piece of network hardware
has a unique six byte MAC address (2<sup>48</sup>, 281,474,976,710,656 or over 281 trillion
addresses.) which is not as big an address as IPv6 but slightly larger than the <a
href="http://www.usdebtclock.org/">US debt</a> and probably big enough for a little while.</p>

<p>The data is often then further packetized to suite the specific medias physical phenomenon. For
example optical fiber uses SONET which puts multiple pieces of user data into frames that travel
from optical end point to end point through repeates before being unbundled and turned back into
electrical signals and routed. SONET can actually do some frame routing without turning the optical
signals back into electrical signals.</p>

<h2 id="ports">More About Ports</h2>

<p>Ports on a computer are usually represented by a number from 0 to 65 K. The port concept is used
to connect specific protocals to specific software that listens on the port. For example, HTTP
usually used port 80 for inbound connections to the HTTP server like Apache on Unix like computers.
When an HTTP connections is made by a client browser the Apache software will communicate with he
client using the outbound port specified by the client (port above 1,023).  <a
href="http://en.wikipedia.org/wiki/Well_known_ports#Well-known_ports">Well known ports</a> reside
between 0 and 1,023. The inbound port is well know to clients and is specified via <a
href="http://www.ietf.org/rfc/rfc1700.txt">RFC 1700</a>. The outbound port is a uniquely assigned
ephemeral port usually above 32,768 (Linux) to be used by the host to communicate with the client for
the duration of the TCP connection.</p>

<p>There are many well know ports that are used by standard TCP/IP protocals, for example here are a
few very well know ports: FTP: 20, SSH: 22, Telnet: 23, SMTP: 25, DNS: 53, HTTP: 80, POP3: 110, NTP:
123, IMAP: 143, IRC: 194, HTTPS: 443 etc.</p>

<h2 id="httpexample">HTTP Example</h2>

<p>HTTP (Hypertext Transfer Protocal) is an <i>application</i> layer protocal. Using TCP/IP the
  client browser creates a <b>Request</b> and sends it to a HTTP host who is listening on well known
  port 80. HTTP hosts can listen on other private posts which are known only to specific
  privilaged users. This is often done by client help systems and other services know to a specific
  piece of client software.</p>

<p>The HTTP protocal has gone through a couple of revisions. The original HTTP/1.0 specified three
  commands: GET, POST and HEAD. The recent HTTP/1.1 specifies 5 additional commands: OPTIONS, PUT,
  DETLTE, TRACE and CONNECT. By far the most use command is GET followed by POST and HEAD. The other
  HTTP/1.1 commands are actually seen infrequently.</p>

<p>The protocal is all plain text and is broken into a <b>Request</b> and a <b>Response</b>. The
<b>Request</b> is sent to the host by the client and usually asks for a specific web page. The
<b>Response</b> is sent back to the client from the host and if everything was successful it has the
HTML of the web page and a header.</p>

<p>The <b>Request</b> for a web page looks like this:</p>

<pre>
GET /howtheinternetworks.php HTTP/1.1
Host: bartonphillips.org
Connection: keep-alive
Cache-Control: no-cache
Pragma: no-cache
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8,de-DE;q=0.6,de;q=0.4
Cookie: PokerClub=10
</pre>

<p>This <b>Request</b> is placed inside a TCP/IP packet. So there is an IPv4 header (in most cases)
which is between 20 and 60 bytes and has the client's IP address and the hosts IP address along with
additional control data. Following the IP header is the TCP header which has the source port,
destination port, sequence number and some additional information. The TCP header is another 20
bytes. These two headers are followed by the <b>Request</b> information (above).</p>

<p>The first line tells the host that this is a GET request and that the file to server is
'/howtheinternetwork.php'. The second line identifes the host. The server uses the the 'Host:'
information to determin the virtual host that is being requested. The URI bartonphillips.com
resolves via DNS (Domain Name Service) to an IPv4 address, in this case 192.249.115.106. However,
192.249.115.106 also is the home of bartonphillips.org, bartonphillips.net, granbyrotary.org and
several other websites. The rest of the lines tells the Apache server how to return the data.</p>

<p>The Apache web server listening on the well known port 80 at IP address 192.249.115.106 looks at
the 'Host:' line (line two) and uses that URI to access the virtual host information for
bartonphillips.com. The web server looks in the document root for that virtual host for the file
mentioned, 'howtheinternetworks.php'. Once the server finds the file it processes the information in
the file and creates a <b>Response</b> header and attaches the processed information in HTML format
to that header. An IP and TCP header are prepended to the <b>Response</b> and returned to the
client.</p>

<p>The <b>Response</b> header looks something like this:</p>

<pre>
HTTP/1.1 200 OK
Date: Thu, 17 Apr 2014 22:49:16 GMT
Server: Apache
Vary: Accept-Encoding,User-Agent
Content-Encoding: gzip
Content-Length: 5631
Keep-Alive: timeout=3, max=100
Connection: Keep-Alive
Content-Type: text/html; charset=utf-8
</pre>

<p>The HTML web page follows the <b>Response</b> header and starts out like this:</p>

<pre>
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;title&gt;How the Internet Works&lt;/title&gt;
  &lt;meta charset='utf-8'&gt;
  &lt;meta name="Author"
     content="Barton L. Phillips, mailto:barton@bartonphillips.org"&gt;
  &lt;meta name="description"
     content="How the Internet Works"&gt;
</pre>
<p>Followed by a lot more HTML.</p>

<p>The web server host send the <b>Response</b> information back to the client's IP address.  The
client's TCP/IP stack takes the returned information apart, finds the port number of the client's
browser and sends the <b>Response</b> data to the browser.</p>

<hr>
<div id="otherarticles">
  <p>Other articles in this series:</p>
  <ul>
    <li><a href="http://www.bartonphillips.com/historyofinternet.php">The History of the Internet</a></li>
    <li><a href="http://www.bartonphillips.com/howtheinternetworks.php">How the Internet Works</a></li>
    <li><a href="http://www.bartonphillips.com/howtowritehtml.php">How to Write HTML</a></li>
    <li><a href="http://www.bartonphillips.com/buildawebsite.php">So You Want to Build a Website</a></li>
  </ul>
</div>

<hr>
<?php echo $footer; ?>


