# CHANGES (oldest first)
__BLP 2021-10-29__ -- Add gps and mapping.
__BLP 2018-03-06__ -- break up index.php into index.i.php, index.js and index.css.  
Also create the *articles* directory directory and moved all of the *__Helpful Programs and Tips__* 
programs into that directory. This is an attempt to clean up the directory structure.



__BLP 2021-09-15 through 2021-09-22__ -- I have changed the way we identify 'Me'. If I am using another CPU with a different IP then I should
first log into my site *bartonphillips.com*. This will cause the IP to be added to the *members* table in the *bartonphillips*
database. It will also add the IP to the *myip* table in the *barton* database. I use the *myip* table to load the IPs that I have
used in SiteClass into *$this->myIp*. This is new logic so watch out.

I have also changed the *SiteId* cookie to have the IP address plus the email address as "\<ip\>:\<email\>".

__BLP 2021-09-22__ -- move adminsites.php from bartonphillipsnet to bartonphillips.com.

__BLP 2021-09-23__ -- changed layout of the members table in the *bartonphillips* database. Removed *id* and now only *email* is a key.

__BLP 2021-10-01__ -- removed symlinks to __bartonlp__ and __wordpress__. I have cleaned up the __bartonlp__ directory so there is amost nothing there
accept the __other__, __scripts__, __apache-conf__ and __reiverside__ directories.