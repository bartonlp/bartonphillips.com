#!/usr/bin/perl -w

use strict;

use DBI();

my @lines;

while(my $line = <DATA>) {
  push @lines, $line;
} 

my $DBH;

$DBH = DBI->connect('dbi:mysql:bartonphillipsdotorg', '3342', 'lueQu5saig2l') or die "Can't connect";

foreach my $line (@lines) {
  next if $line =~ /^\s*$/;
  chomp($line);
  $line =~ /"(.*?)",<(.*?)>/;
  my $name = $1;
  my $email = $2;

  print ":$name: $email:\n";
  $DBH->do("insert into fairway  (name, email) values('$name', '$email')") or die($DBH->errstr);
}

__END__
"Natascha",<nataschao@comcast.net>
"",<goflaherty@comcast.net>
"Barton Phillips",<bartonphillips@gmail.com>
"Bill Pawson",<wpawson@westbaydecorating.com>
"Dale Floren",<dafloren71@hotmail.com>
"Darren Custer",<drealtylending@gmail.com>
"David Palmer".<davep@nopaint.com>
"DeQuasie, Kellie L.",<Kellie.DeQuasie@anthem.com>
"Jody Panian",<Stjpanian@msn.com>
"",<kimcarver_@msn.com>
"Lou Hines",<louhines@st-tel.net>
"Loyal Steube",<loyal@c21winterpark.com>
"Mark Penny",<markjpenny@gmail.com>
"",<mblount@powermotivecorp.com>
"",<Phyllis@c21winterpark.com>
"Randolph H. Freking",<Randy@frekingandbetz.com>
"Sissy Blount",<sissyblount@me.com>
"",<t_rexc@msn.com>