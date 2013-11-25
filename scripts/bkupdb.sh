#!/bin/bash
# Backup the database before starting.
# I create a file CE_BACKUP.sql which can be used to create a new database
cd /var/www/bartonphillips.com/htdocs
dir=other
# Day of week Mon-Sun
#dayOfWeek=`date | cut -d " " -f 1`
# date will show 'Thu Mar  3 12:38:43 PST 2005' which has two spaces
# between Mar and 3, but
# 'Mon Feb 28 12:38:43 PST 2005' has only one space. Each space is a
# seperator so -f 4 in march gets '3' and -f 4 in Feb gets the time.
# use sed to remove the extra space if it is there.
#day=`date | sed -e 's/  / /g' | cut -d " " -f 3`
#filename="GR_BACKUP.$dayOfWeek.$day.sql"
bkupdate=`date +%B-%d-%y`
filename="BLP_BACKUP.$bkupdate.sql"
# If we have an argument -d then we delete the file first
#if [ "$1" ==  "-d" ]; then
#shift
#rm $dir/GR_BACKUP.$dayOfWeek.*.sql.gz
#fi

#MySQL database: bartonphillipsdotorg
#MySQL username: 3342
#MySQL password: lueQu5saig2l

mysqldump --user=3342 --no-data --password=lueQu5saig2l bartonphillipsdotorg > $dir/bartonphillips.schema
mysqldump --user=3342 --add-drop-table --password=lueQu5saig2l bartonphillipsdotorg >$dir/$filename
#the schema.pl program needs the keys and fourign keys to have a format
#of xxxId and xxxId_fk. The granbyranch database does not have that yet!
#schema.pl granbyrotary.schema > granbyrotary.ref

gzip $dir/$filename

