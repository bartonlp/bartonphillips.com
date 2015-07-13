#!/usr/bin/perl -w
# Update the Sitemap.xml file
# update the <lastmod> field with current last modified time

use strict;
use Getopt::Long qw(GetOptions);
use Pod::Usage;
use File::Basename;

my $help = 0;
my $man = 0;

GetOptions('help|?' => \$help,
	   'man' => \$man) or pod2usage(2);

pod2usage(1) if $help;
pod2usage(-exitval => 0, -verbose => 2) if $man;

# read a sitemap xml file from standard in

while(<>) {
  print;
  next if not /<loc>/;

  chomp;

  /<loc>(.*?)<\/loc>/;
  
  my $url = $1;

  $url =~ /\/([^\/]*)$/;
  my $base = $1;

  next if not -e $base;
  
  my($dev,$ino,$mode,$nlink,$uid,$gid,$rdev,$size,
   $atime,$mtime,$ctime,$blksize,$blocks)
      = stat("$base");

  my($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($mtime);

  while(<>) {
    if(not /<lastmod>/) {
      print;
      next;
    }

    /<lastmod>(.*?)<\/lastmod>/;
    printf("\t\t<lastmod>%d-%02d-%02d</lastmod>\n", $year+1900, $mon+1, $mday);
    last;
  }
}

__END__

=head1 SYNOPSIS

updatemodtime.pl [options] <xml site map file>

=head1 OPTIONS

B<help>:              Print out a breif help statement.

B<man>:               Print out the full man page.

=head1 DESCRIPTION

Read from stdin the current Sitemap.xml. Gather the last modified information and output
the updated xml file to stdout
 
=cut
