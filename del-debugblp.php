<?php
// BLP 2014-03-07 -- delete the /tmp/debugblp.txt file. This has owner and group 'apache'
unlink("/tmp/debugblp.txt");
echo "Deleted /tmp/debugblp.txt";
