<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

/*
// HTML to PDF
// http://mpdf1.com/manual/ Documentation
include("/home/barton11/includes/MPDF57/mpdf.php");
$mpdf = new mPDF;
*/

$S = new Blp; // takes an array if you want to change defaults
$h->extra =<<<EOF
<style>
#images table, #images img {
  width: 90%;
}
.important {
  color: red;
  font-weight: bold;
  font-style: italic;
}
blockquote {
  margin-top: 10px;
  margin-bottom: 10px;
  margin-left: 10px;
  padding-left: 15px;
  border-left: 5px solid black; /*#ccc;*/
}
@media print {
  #images {
    page-break-before: always;
  }
  #images hr {
    page-break-before: always;
  }
}  
</style>
EOF;

$h->title = "History of the Internet";
$h->banner = "<h1>History of the Internet</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

$article =<<<EOF
<article>
  <section id="overview">
  <h2>Overview, An Incredible Journey</h2>

<p>From the beginning of electronic communication with the telegraph in 1833 to the present it has
all happened in 181 years. The pace of technological advancements has been accelerating at what
seems like an exponential rate. It is hard to believe that when I was born in 1944 TV was just a
nascent technology, vinyl records where still 78 RPM, long distant travel was via train
and transatlantic trip were via ship.</p>

<p>The <b>record</b>, that is vinyl records, had a 100 year run, while the CD, first pressed in 1982, has
already been mostly replaced by solid state devices and smartphones via the Internet.
And the Internet has gone from a glimmer in a few scientists eyes to a trillion dollar business
in a little over 30 years.</p>

<p>Communication speed has gone from the teletype at 75 <a href="#bitsbytes">bits per second</a>
(bps) in the 1960's to gigabits per second (Gbs) speeds today. When the Internet was born in the
late 1960's it ran at 1,200 bps and today the
<a href="http://en.wikipedia.org/wiki/Internet_backbone">Internet backbone</a> using fiber optic cable
and transmission protocols like SONET (Synchronous Optical Networking) and ATM (Asynchronous
Transfer Mode) hits over 400 Gbs. This incredible speed improvement has made possible music and
video on demand over the Internet as well as telephone and data that supports the financial and
business communities around the world.</p>

<p>This phenomenal growth is due to the development of the transistor, integrated circuits and the
microcomputer. Without these technologies <b>packet switching networks</b> which are at the heart of
the Internet would not be possible.  The development of packet-switching time domain multiplexing
required the speed of the microcomputer. The old <b>circuit switching</b> technology of the
telephone companies just was not viable for a world wide data communication grid.</p>

<p>The following timeline highlights some of what I feel were the seminal breakthroughs of the last
century and a half.</p>
  </section>
  <section id="timeline">
<h2>Timeline of Electronic Communications</h2>
<ul>
  <li><span class="important">1833</span> Telegraph: Carl Friedrich Gauss and Wilhelm Weber, G&#246;ttingen
    Germany.</li>
  <li>1837 Samuel Morse, the telegraph in the USA and Mores Code.</li>
  <li>1867 American, Sholes the first successful and modern typewriter.</li>
  <li><span class="important">1876</span> Alexander Graham Bell patents the electric telephone.</li>
  <li>1877 Thomas Edison patents the phonograph - with a wax cylinder as recording medium.</li>
  <li>1887 Emile Berliner invents the gramophone - a system of recording which could be used over and
    over again. </li>
  <li>1888 George Eastman patents Kodak roll film camera.</li>
  <li>1894 Guglielmo Marconi improves wireless telegraphy.</li>
  <li><span class="important">1902</span> Guglielmo Marconi transmits radio signals from Cornwall to
    Newfoundland -  the first radio signal across the Atlantic Ocean.</li>
  <li><span class="important">1906</span>
    <a href="http://en.wikipedia.org/wiki/Lee_De_Forest">Lee Deforest</a> invents the electronic
    <a href="http://en.wikipedia.org/wiki/Vacuum_tube">amplifying tube</a> or triode -
    this allowed all electronic signals to be amplified improving all electronic communications </li>
  <li>1923 The television or iconoscope (cathode-ray tube) invented by Vladimir Kosma Zworykin -
    first television camera.</li>
  <li>1939 Scheduled television broadcasts begin.</li>
  <li><span class="important">1944</span> Barton Phillips born April 11.<br>
    <span class="important"><a href="#tubecomputer">Computers</a>
      put into public service - government owned - the age of Information Science
      begins.</span><br>The <a href="http://en.wikipedia.org/wiki/Colossus_computer">Colossus</a> at Bletchley Park England was used at the end
      of World War II to break encrypted German messages.
      Ten Colossi were in use by the end of the war.</li>
  <li><span class="important">1948</span>
    <a href="http://en.wikipedia.org/wiki/Transistor">Transistor</a> invented at Bell Labs -
      enabling the miniaturization of electronic devices.</li>
  <li>1948-1950 Cable TV and subscription TV services.</li>
  <li>1950-1961 Development of T-1 transmition lines by Bell Labs.</li>
  <li><span class="important">1951</span> Computers are first sold commercially.</li>
  <li>1952 CERN (&quot;Conseil Europ&#233;en pour la Recherche Nucl&#233;aire&quot; or 
    European Organization for Nuclear Research) founded in Switzerland.</li>
  <li><span class="important">1958</span> Integrated Circuits invented enabling the further miniaturization
    of electronic devices and  computers.</li>
  <li><span class="important">1960</span> Packet Switching: Paul Baran, Donald Davies and Leonard Kleinrock initial work.</li>
  <li>1961 Host based email CTTS systems (Compatible Time-Sharing System. Big Main Fraims)</li>
  <li><span class="important">1964</span> Barton Phillips graduates from UCLA and enter
    the Air Force.</li>
  <li><span class="important">1965</span>
    <ul>
      <li>DARPA (Defense Advanced Research Projects Agency) commissioned a
        study of decentralized switching systems.</li>
      <li>First demonstration net between MIT's Lincoln Lab and System Development Corporation
        in California (1200 bits/sec).</li>
    </ul>
  </li>
  <li><span class="important">1969</span>
    <ul>
      <li>ARPANET (Advanced Research Projects Agency Network) the first Internet started.
        Backbone running at 50 Kbits/sec.</li>
      <li><a href="http://en.wikipedia.org/wiki/Request_for_Comments">Request for Comments</a>
        (RFC) started</li>
    </ul>
  </li>
  <li>1970 Barton Phillips returns to US from the Air Force</li>
  <li>1971
    <ul>
      <li>The computer <a href="http://en.wikipedia.org/wiki/Floppy_disk">floppy disk</a>
        invented.</li>
      <li>The <a href="http://en.wikipedia.org/wiki/Microprocessor">microprocessor</a> invented.
        Three projects delivered a microprocessor at about the same time:
        Garrett AiResearch's Central Air Data Computer (CAD8),
        Texas Instruments' TMS 1000 (September),
        and Intel's 4004 (November).
  </li>
    </ul>
  </li>
  <li>1972 Ray Tomlinson invented network email and the '@' sign.</li>
  <li><span class="important">1974</span> TCP/IP (Transmission Control Program/Internet Protical RFC 675.
    Vinton Cerf, Yogen Dalal and Carl Sunshine).</li>
  <li>1976
    <ul>
      <li>Barton Phillips bought the <a href="#6502">KIM 1</a> 6502 Computer kit:
        Hex keypad, 7 segment display, 1K RAM, 8K ROM.</li>
      <li>The S100 bus Altair 8800 with the Intel 8080 processor became available 
        along with the <a href="#8080">IMSAI 8080</a>.</li>
      <li>March: X.25 Network standard approved.</li>
    </ul>
  </li>
  <li>1977 April: Barton Phillips purchased the Apple I home computer also 6502 based.
    The Apple I had 4 or 8 Kbytes of RAM and Integer Basic in ROM. It also had a casset tape interface
    for reading and writing data via a casset player.
  </li>
  <li>1978
    <ul>
      <li>October: Barton Phillips joins
        <a href="http://en.wikipedia.org/wiki/Micropolis_Corporation">Micropolis Corp.</a>
        a floppy disk manufacture.
        Between 1978 and 1983 Barton wrote disk OS, Basic Interpreter, Assembler/Linker and
        Editor for the Micropolis products. In 1983 Micropolis stopped marketing its OS.</li>
      <li>1978 X.25 provided the first international and commercial packet switching network, 
        the "International Packet Switched Service" (IPSS).</li>
    </ul>
  </li>
  <li>1979 First cellular phone communication network started in Japan.</li>
  <li><span class="important">1980</span>
    <a href="http://en.wikipedia.org/wiki/Tim_Berners-Lee">Tim Berners-Lee</a> at CERN in Switzerland developed
    <a href="http://en.wikipedia.org/wiki/ENQUIRE">ENQUIRE</a> a hypertext program.
    He also created <a href="http://en.wikipedia.org/wiki/HTML">HTML</a>
    (Hyper Text Markup Language).</li>
  <li>1981 <a href="http://en.wikipedia.org/wiki/IBM_Personal_Computer">IBM PC</a> first sold.</li>
  <li>1982
    <ul>
      <li>SMTP (Simple Mail Transport Protical) RFC 821.</li>
      <li>April: Sony Records presses first CD (Compact Disk)</li>
    </ul>
  </li>
  <li>1983
    <ul>
      <li><a href="http://en.wikipedia.org/wiki/Ethernet">Ethernet</a>,
        which was introduced in 1980 was standardized IEEE 802.3.</li>
      <li>Time magazines names the computer as
        <a href="http://content.time.com/time/covers/0,16641,19830103,00.html:>
          Machine of the Year.
        </a>
      </li>
    </ul>
  </li>
  <li>1984
    <ul>
      <li>Number of network hosts breaks 1,000</li>
      <li>Apple <a href="http://en.wikipedia.org/wiki/Macintosh">Macintosh</a> released.</li>
      <li><a href="http://en.wikipedia.org/wiki/IBM_Personal_Computer/AT">IBM PC AT</a>
        released using Intel 80286</li>
      <li>ARPANET backbone via T-1 at 1.5 Mbits/sec.</li>
      <li>POP1 (Post Office Protical 1) RFC 918.</li>
    </ul>
  </li>
  <li>1986
    <ul>
      <li>IMAP (Internet Mail Access Protocol) was designed by Mark Crispin RFC 1064.</li>
      <li>SGML (Standard Generalized Markup Language) ISO 8879:1986.</li>
    </ul>
  </li>
  <li>1987 Number of network hosts breaks 10,000</li>
  <li>1988
    <ul>
      <li>ADSL (asymmetric digital subscriber line) patented.</li>
      <li>POP3 RFC 1081 (the current standard)</li>
    </ul>
  </li>
  <li><span class="important">1989</span>
    <ul>
      <li>Tim Berners-Lee coined
        <a href="http://en.wikipedia.org/wiki/World_Wide_Web">World Wide Web or WWW</a>
        again at CERN.</li>
      <li><a href="http://en.wikipedia.org/wiki/National_Science_Foundation_Network">NSFNet</a>
        takes over from ARPANET and becomes the principal internetwork backbone.</li>
      <li>Number of network hosts breaks 100,000</li>
    </ul>
  </li>
  <li>1990
    <ul>
      <li>HTTP (Hyper Text Transport Protical),</li>
      <li>HTML (Huper Text Markup Protical), first server (CERN httpd) and the first browser 
        all created by Tim Berners-Lee and Robert Cailliau all running on a
        <a href="http://en.wikipedia.org/wiki/NeXT">NeXT</a> computer.</li>
      <li>Nicola Pellow created a browser that could run on almost all computers called
        the "Line Mode Browser".</li>
      <li>URL of first web site: <a href="http://info.cern.ch">http://info.cern.ch</a></li>
    </ul>
  </li>
  <li>1991
    <ul>
      <li>January: first HTTP server outside of CERN was activated.</li>
      <li>Comercial restriction on Internet lifted.</li>
      <li><a href="http://en.wikipedia.org/wiki/ANSNET">
          ANSNet</a> Backbone via T-3 at 45 Mbits/sec.</li>
    </ul>
  </li>
  <li>1992
    <ul>
      <li>April: Erwise first graphical browser available for systems other than the
        NeXT computer.</li>
      <li>Number of network hosts breaks 1,000,000</li>
    </ul>
  </li>
  <li><span class="important">1993</span>
    <ul>
      <li>WWW (World Wide Web).<br>
        January: 50 web servers in the world.<br>
        October: 500 web servers in the world.</li>
      <li>Mosaic web browser released by National Center for Supercomputing Applications (NCSA) 
        at the University of Illinois at Urbana-Champaign (UIUC), led by Marc Andreessen.
        Funding for Mosaic came from the "High-Performance Computing and Communications Initiative",
        a funding program initiated by then Senator Al Gore's "High Performance Computing and 
        Communication Act" of 1991 also known as the Gore Bill.</li>
      <li>June: Cello by Thomas R. Bruce was the first browser for Microsoft Windows.</li>
      <li>August: The NCSA released Mac Mosaic and WinMosaic.</li>
    </ul>
  </li>
  <li><span class="important">1994</span>
    <ul>
      <li>Private sector assumes responsibility for the Internet.
        Backbone via ATM at 145 Mbits/sec</li>
      <li>April: Netscape founder by Mark Andreessen and James H. Clark. Netscape Navigator born.</li>
      <li>Amazon founded.</li>
    </ul>
  </li>
  <li>1995
    <ul>
      <li>NFSNet backbone service decomissioned.</li>
      <li>HTML 2.0 published as IETF RFC 1866.</li>
    </ul>
  </li>
  <li>1996 Cable Internet. Rogers Communications introduced the first cable modem service
    in Canada.<br>
    January: Google started as a research project by Larry Page and Sergey Brin at 
    Stanford University.</li>
  <li>1997 HTML 3.2 published as a W3C Recommendation.</li>
  <li>1998
    <ul>
      <li>September: Google incorporated.</li>
      <li>HTML 4.0 published as a W3C Recommendation.</li>
    </ul>
  </li>
  <li>1999-2001 "Dot Com" Boom, then bust.</li>
  <li>2000 Apple Computer releases <a href="http://en.wikipedia.org/wiki/Mac_os_x">Mac OS X</a>
    a Unix lookalike operating system.</li>
  <li>2001 January: Wikipedia launched.</li>
  <li>2004 February: Facebook launched.</li>
  <li>2005 YouTube launched.</li>
  <li>2006
    <ul>
      <li>SONET OC768 40 Gbit/sec optical fiber. <br>
        Theoretical Limit to fiber optical cable is one terabit or one trillion bits per second.</li>
      <li>Apple Computer switches from PowerPC processor to Intel thus obsoleting millions of
        systems in businesses and schools.</li>
    </ul>
  </li>
  <li>2008
    <ul>
      <li>January: HTML5 was published as a Working Draft by the W3C.</li>
      <li>October 23: AT&T announced the completion of upgrades to OC-768 on
        80,000 fiber-optic wavelength miles of their IP/MPLS (Multiprotocol Label Switching)
        backbone network.</li>
    </ul>
  </li>
  <li>2012
    <ul>
      <li>December: W3C designated HTML5 as a Candidate Recommendation.</li>
      <li>NEC Corp. broke an ultra-long haul Internet speed record when
        it successfully transmitted data at 1.15 terabits/sec over 6,213 miles.</li>
    </ul>
  </li>
  <li>2014 The W3C (World Wide Web Consortium) plans to finalize the HTML 5 standard by July.</li>
</ul>
</section>
<section id="speed">
<h2>Transmition Speed Timeline</h2>
<ul>
  <li>Mid-1960: Early ARPANET 1200-2400 bits/sec</li>

  <li>1970's: ARPANET 50 Kbits/sec.</li>

  <li>Mid-1980's: LAN (Local Area Network: Ethernet, Token Ring) 10 Mbits/sec.<br>
    WAN (Wide Area Network: modems, T-1) 300-2400 bits/sec to 1.5 Mbits/sec.</li>

  <li>1990's: WAN (T-1, ADSL, T-3, ATM) 1.5 Mbits/sec to 145 Mbits/sec.
    ADSL: downstream: 200-400 Mbits/sec, upstream: 384 Kbits/sec to 20 Mbits/sec.</li>

  <li>2000's: WAN (SONET-OC-192) 10 Gbits/sec.</li>

  <li>201x: WAN (SONET-OC-768) 40 Gbits/sec.</li>
</ul>

<p>In 2000 there were just under 150 million dial-up subscriptions in the 34 OECD (Organisation for
Economic Co-operation and Development) countries and fewer than 20 million broadband
subscriptions.</p>

<p>By 2004, broadband had grown and dial-up had declined so that the number of subscriptions were
roughly equal at 130 million each.</p>

<p>In 2010, in the OECD countries, over 90% of the Internet access subscriptions used broadband,
  broadband had grown to more than 300 million subscriptions, and dial-up subscriptions had declined
  to fewer than 30 million.</p>
</section>

<section id="connections">
<h2>Making the Connections</h2>

<p>The ARPANET, predecessor to the Internet, started with an inspiring vision of a
&quot;galactic&quot; network, practical theory about packet switching, and a suite of standardized
protocols. But none of this would have mattered if there hadn't also been a way to make and maintain
connections.</p>

<p>In 1966-67 Lincoln Labs in Lexington, Massachusetts, and SDR in Santa Monica, California, got a
grant from the DOD to begin research on linking computers across the continent. Larry Roberts,
describing this work, explains:</p>

<blockquote>
&quot;Convinced that it was a worthwhile goal, we set up a test network to see where the problems
would be. Since computer time-sharing experiments at MIT and Dartmouth had demonstrated that it was
possible to link different computer users to a single computer, the cross country experiment built
on this advance.&quot;
</blockquote>

<p>(i.e. Once timesharing was possible, the linking of remote computers was also
possible.) Roberts reports that there was no trouble linking dissimilar computers. The problems, he
claims, were with the telephone lines across the continent, i.e. that the throughput was inadequate
to accomplish their goals.</p>

<p>The first ARPANET link was established between the University of California, Los Angeles (UCLA)
and the Stanford Research Institute at 22:30 hours on October 29, 1969</p>

<p><b>Packet switching</b> resolved many of the issues identified during the pre-ARPANET,
time-sharing experiments. But higher-speed phone circuits also helped. The first wide area network
(WAN) demonstrated in 1965 between computers at MIT's Lincoln Lab, ARPA's facilities, and the System
Development Corporation in California utilized dedicated 1200 bps circuits. Four years later, when
the ARPANET began operating, 50 Kbps circuits were used. But it wasn't until 1984 that ARPANET
traffic levels were such that it became more cost-effective to lease T1 lines (1.5 Mbps) than to
continue using multiple 50 Kbps lines.</p>

<p>In the late 1960's and early 1970's there were a number of separate nascent networks developed
by States, Universities, and governments: NPL, Merit Network, CYCLADES, X.25. The problem with all
these different networks was that they all &quot;spoke&quot; different languages/protocols thus
internetworking was difficult if not impossible.</p>

<p>In 1973 Vinton Cerf, the developer of the existing ARPANET Network Control Program (NCP) protocol,
joined Robert E. Kahn to work on open-architecture interconnection models with the goal of designing
the next protocol generation for the ARPANET. This was TCP/IP.</p>

<p>By the summer of 1973 Kahn and Cerf had worked out a fundamental reformulation in which the
differences between network protocols were hidden by using a common internetwork protocol, and
instead of the network being responsible for reliability, as in the ARPANET, the hosts became
responsible. Cerf credits Hubert Zimmermann and Louis Pouzin, designer of the
<a href="http://en.wikipedia.org/wiki/CYCLADES">CYCLADES</a> network, with
important influences on this design.</p>

<h2>Circuit Switching vs. Packet Switching:</h2>

<p>Circuit switching is a method which sets up a limited number of dedicated connections of constant
bit rate and constant delay between nodes for exclusive use during the communication session.  It is
a methodology of implementing a telecommunications network in which two network nodes establish a
dedicated communications channel (circuit) through the network before the nodes may communicate. The
circuit guarantees the full bandwidth of the channel and remains connected for the duration of the
communication session. The circuit functions as if the nodes were physically connected as with an
electrical circuit.</p>

<p>Packet switching divides the data to be transmitted into packets transmitted through the network
independently. In packet switching, instead of being dedicated to one communication session at a
time, network links are shared by packets from multiple competing communication sessions, resulting
in the loss of the quality of service guarantees that are provided by circuit switching.</p>

<p>Packet switching also imposes overhead burdens because each packet must have information that
delimits the packet. In TCP/IP the IP header comes first and is used to direct the packets from the
source to the destination and identify the type of service being provided. The IP header is like a
letters envelope which contains an address and a return address. The TCP header comes after the IP
header and contains information about the transmission including endpoint ports, sequencing
information and the data. The data may (an usually does) have other headers that describe the
specific service, for example HTTP, IMAP, POP3, FTP etc.<p>

<p>When a web page is transmitted from the server to the client there are usually many TCP/IP packets
of data involved. These packets that represent the web page may take different routes to get to their
final destination and may in fact arrive at the destination out of order. It is the information in the
TCP header that allows the client (destination) to reassemble the web page from the many packets
correctly.</p>

<p>A good analogy is the Post Office. If we were going to send a large manuscript in chapters as they
were completed we would put the manuscript chapters into envelopes and address the envelopes with
the destination address and the return address. We would also include information in the envelope
describing the sequence of the chapters. The envelope is the IP header and the information inside
the envelope is the TCP header and data. We need the TCP type of information in the envelope because
as we all know letters can be received out of sequence and therefore we need some information to
let us know how to reassemble the manuscript.</p>
</section>

<h2 id="bitsbytes">Bits Bytes Binary Hex</h2>

<p>Modern Digital Computers think in binary: ones and zeros. We have used the phrase
&quot;bits per second&quot; or bps a lot in this history. In communication a bit is generally thought
of as a one or a zero. Depending on the encoding scheme used in the communication stream eight bits
may represent a character or byte. I say may because different encoding schemes can use more than
eight bits in order to cope with transmission phenomenons. So when we say that a early teletype ran
at a rate of 75 bits per second (bps) that means that they typed about nine characters a second.</p>

<p>A byte is eight binary digits, for example the number seven decimal in binary is 0111. The decimal
number Fifteen (15) is 1111 in binary. The decimal number sixteen is 0001,0000 in binary and takes up
two bytes while fifteen takes only one byte. As I said in the previous paragraph we usually think of
a byte as being eight bits.</p>

<p>So what is HEX? HEX stands for hexadecimal which is a number system with a base of sixteen.
The first sixteen hexadecimal numbers are 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, A, B, C, D, E, F. The
number sixteen decimal is 10 HEX. Computer engineers use HEX because it works well with binary.
For example the number 47 decimal is 0010,1111 binary and 2F HEX. Binary is powers of two so the
binary two bytes shown are (128)(64)(32)(16),(8)(4)(2)(1). 0010,1111 is then 32+8+4+2+1 or 47 decimal.
Do you see the relation between binary and HEX? The HEX number 2F represents two bytes in
binary 0010 (2) and 1111 (F). Each HEX digit is a byte. As you can see binary converts easily
into HEX but not easily into decimal. Other number bases that have been popular are base 8 (octal)
and base 12 (duodecimal) to a much lesser extent.</p>


<h2>From Tim Berners-Lee's first message (web page):</h2>

<blockquote>&quot;The World Wide Web (WWW) project aims to allow all links to be made to any information
anywhere.  [...] The WWW project was started to allow high energy physicists to share data, news,
and documentation. We are very interested in spreading the web to other areas, and having gateway
servers for other data. Collaborators welcome!&quot;</blockquote>

<section id="images">
<h2>Images</h2>

<table id="tubecomputer">
<tr><th>Tube Computer</th><tr>
<tr><td>
<a href="http://en.wikipedia.org/wiki/First_computer"><img src="/images/tubecpu.jpg"></a>
</td></tr>
</table>
<hr>

<table id="6502">
<tr><th>6502 CPU</th><th>KIM 1</th><th>Apple I</th></tr>
<tr>
<td>
<img src="/images/6502cpu.jpg">
</td>
<td>
<a href="http://en.wikipedia.org/wiki/KIM-1"><img src="/images/kim1.jpg"></a>
</td>
<td>
<a href="http://en.wikipedia.org/wiki/Apple_1"><img src="/images/Apple1.jpg"></a>
</td>
</tr>
</table>

<hr>
<table id="8080">
<tr><th>8080 CPU</th><th>S100 Board</th><th>IMSAI 8080</th><tr>
<tr>
<td>
<img src="/images/D8080A-1.jpg">
</td>
<td>
<img src="/images/8080cpu.jpg">
</td>
<td>
<a href="http://en.wikipedia.org/wiki/IMSAI_8080"><img src="/images/imsai8800.jpg"></a>
</td>
</tr>
</table>
</section>
</article>
EOF;

if($_GET['page'] == 'print') {
  echo <<<EOF
<!DOCTYPE HTML>
<html>
<head>
<style>
#images table, #images img {
  width: 90%;
}
.important {
  color: red;
  font-weight: bold;
  font-style: italic;
}
blockquote {
  margin-top: 10px;
  margin-bottom: 10px;
  margin-left: 10px;
  padding-left: 15px;
  border-left: 5px solid black; /*#ccc;*/
}
@media print {
  #timeline, #speed, #connections, #images {
    page-break-before: always;
  }
  #images hr {
    page-break-before: always;
  }
}  
</style>
</head>
<body>
$h->banner
$article
</body>
</html>
EOF;
  exit();
}

/*
$mpdf->WriteHTML($article);
$mpdf->Output('/tmp/pdfpage.pdf', 'F');
*/

echo <<<EOF
$top
$article
<hr>
<input type='image' id='printbtn' src='/images/print.gif' onclick="printit();"
  style='width: 100px'/><br>
<!--<a href="download.pdf">Download PDF version</a> of article-->
<hr>
<script>
function printit() {
  window.location='historyofinternet.php?page=print';
}
</script>
$footer
EOF;
?>
