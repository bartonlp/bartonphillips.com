#!/usr/bin/perl -w
# Designed to be run as a cron job.
# Scan the meetings table and send emails to speakers

use strict;
use POSIX qw(mktime);
use DBI();
my $DBH;

open(FILE, "<members.cvs") or die "Can't open file $!\n";

$DBH = DBI->connect('dbi:mysql:bartonphillipsdotorg', '3342', 'lueQu5saig2l') or die "Can't connect";

while(<FILE>) {
  s/,,/," ",/g;
  # "Last Name","First Name","Last Name","Address","Phone","Alt Phone","Email"
  my $x =  /^"(.*?)","(.*?)",".*?","(.*?)","(.*?)","(.*?)","(.*?)"$/;

  #print "x=$x: 1='$1', 2='$2', 3='$3', 4='$4', 5='$5', 6='$6'\n";
  
  my $query = "insert into wilderness (lname, fname, address, phone, phone2, email) value('$1','$2','$3','$4','$5','$6')";

  #print "$query\n";
  
  my $sth = $DBH->prepare($query) or die $DBH->errstr;
  $sth->execute() or die $DBH->errstr;
  $sth->finish;
}
close(FILE);

__END__
