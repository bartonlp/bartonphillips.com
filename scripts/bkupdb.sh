#!/bin/bash
# Backup the database before starting.
cd /var/www/bartonphillips.com
dir=other
# Day of week Mon-Sun
#dayOfWeek=`date | cut -d " " -f 1`
# date will show 'Thu Mar  3 12:38:43 PST 2005' which has two spaces
# between Mar and 3, but
# 'Mon Feb 28 12:38:43 PST 2005' has only one space. Each space is a
# seperator so -f 4 in march gets '3' and -f 4 in Feb gets the time.
# use sed to remove the extra space if it is there.
#day=`date | sed -e 's/  / /g' | cut -d " " -f 3`
bkupdate=`date +%B-%d-%y`
filename="BLP_BACKUP.$bkupdate.sql"
echo "Bartonphillips backup "$bkupdate
mysqldump --user=barton --no-data --password=7098653 bartonphillips 2>/dev/null > $dir/bartonphillips.schema
mysqldump --user=barton --add-drop-table --password=7098653 bartonphillips 2>/dev/null >$dir/$filename

gzip --quiet -c $dir/$filename > $dir/$filename.gz
rm $dir/$filename

echo "bkupdb.sh for bartonphillips.com Done"


