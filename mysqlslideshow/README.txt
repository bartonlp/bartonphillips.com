The MySqlSlideshow PHP class has the following files:

1978  addimages.php
2536  addupdateimage.php
8156  browserside.html
323   dbclass.connectinfo.i.php
2923  dbclass.i.php
4496  ie.html
4553  index.php
458   mktable.sql
9048  mysqlslideshow.class.php
2467  mysqlslideshow.php
16015 mysqlslideshow.zip
4357  serverside.php

as well as this README.txt file.

The class is hosted at http://www.bartonphillips.com as well as the "PHP Classes" http://www.phpclasses.org .

If you have not run the online demo do so: http://www.bartonphillips.com/mysqlslideshow

Install:
1) unzip the zip file (you have probably already done this if you are reading the README.txt file)

2) edit the dbclass.connectioninfo.i.php file for your MySql site and then optionally move it to a 
   location that is not in your Apache server path.

3) optionally move the mysqlslideshow.class.php file and the dbclass.i.php file to somewhere that is not in the Apache server
  path.  This is not really necessary unless you have several different virtual sites running that may want to use the class. I for
  example host my sites at http://lamphost.net. I have several different web sites that I maintain there and I don't want to have
  the class library in every sites directories so I put the class under /home/myname/includes along with other things I don't want
  to duplicate.

3) optionally edit the following file if you have moved the dbclass.connectioninfo.i.php, dbclass.i.php or 
   mysqlslideshow.class.php to a location other than where you unzipped the package:
     mysqlslideshow.class.php : if you moved dbclass.i.php
     mysqlslideshow.php       : if you moved either mysqlslideshow.class.php or dbclass.connectinfo.i.php
     addupdateimage.php       : if you moved either mysqlslideshow.class.php or dbclass.connectinfo.i.php
     addimages.php            : if you moved either mysqlslideshow.class.php or dbclass.connectinfo.i.php
     serverside.php           : if you moved either mysqlslideshow.class.php or dbclass.connectinfo.i.php

4) create you MySql database and table. If you have an existing database you want to use then you don't need to 
   create the database. Other wise:

   CREATE DATABASE mysqlslideshow;

   create the database table. The file mktable.sql if sourced will make the table mysqlslideshow. If you want to call your 
   table something else you can edit the mktable.sql file and then either edit the mysqlslideshow.class.php (NOT RECOMMENDED),
   or use the constructor with the additional optional arguments:
   The first three arguments come from the dbclass.conectioninfo.i.php file. Add the forth and fifth arguments for your
   database name and table name.

   $ss = new MySqlSlideshow($Host, $User, $Password, 'YourDatabaseName', 'YourTableName');

   You can create the table by doing:

   mysql use mysqlslideshow < mktable.sql
   
   or inside mysql use the source statement 'source mktable.sql;'

   or just do it the hard way by typing in the create statement.

5) add some images to your database table. There are two easy ways to do this:
   1) use the addupdateimage.php program. From you web browser (hopefully not IE) enter the following in the location area:

      http://yoursite/addupdateimage.php?image=imageFileName&subject=subject text&description=more text here

      If you want the image data saved in the table instead of the path to the image add the &type=image to the line above.
      You can use a relative path or absolute path. Relative paths will be turned into absolute for the database.
      This will add one image.
    
   2) use the addimages.php program. From you web browser (hopefully not IE) enter the following in the location area:

     http://yoursite/addimages.php?path=searchInfo&pattern=pattern

     Again if you want image data rather than a link in the database table add the &type=image to the end.

     The path=searchInfo is a path plus optional conditional like: ../images/*.gif
     If just the path and a '*' then all the files in that path will be looked at. NOTE: ../images will not work!

     The pattern=pattern is optional. If you want to further qualify the file in the pathInfo you can use a PHP/perl style
     pattern. For example if ?path=../images/&pattern=^big.*?(?:ball)|(?:flag)\.jpg
     then all of the file in the ../images directory would be gathered and the pattern would then be applied to each. Say
     you have files "bigredflag.jpg", "bigblueball.jpg" along with many others, the pattern would put only those two into
     the selection list. 
     The program display your selected file with a check box and <input ...> tags for a subject and description. Make your
     selections and click submit.

6) try out the examples serverside.php and browserside.html on your own server. Then start writing your own code. 
   Have fun.

Any questions can be sent to barton@bartonphillips.com I will try to answer reasonable questions.

