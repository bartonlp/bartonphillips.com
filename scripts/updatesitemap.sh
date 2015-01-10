#!/bin/bash
# backup the Sitemap.xml and then create a new one
cd /home/barton11/www
dir=other
bkupdate=`date +%B-%d-%y`
filename="Sitemap.$bkupdate.xml"
scripts/updatemodtime.pl < Sitemap.xml > sitemap.$$
mv Sitemap.xml $dir/$filename
mv sitemap.$$ Sitemap.xml
gzip $dir/$filename

