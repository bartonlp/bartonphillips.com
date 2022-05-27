#!/bin/bash
# backup the Sitemap.xml and then create a new one

cd /var/www/bartonphillips.com
dir=other
bkupdate=`date +%B-%d-%y`
filename="Sitemap.$bkupdate.xml"
scripts/updatesitemap.php > sitemap.$$
mv Sitemap.xml $dir/$filename
mv sitemap.$$ Sitemap.xml
gzip $dir/$filename

find $dir -ctime +30 -type f -exec rm '{}' \;

#echo "updatesitemap.sh for bartonlp.com Done"
