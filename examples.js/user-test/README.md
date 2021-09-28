# bartonlp.com/examples.js/user-test

This directory has programs that use the *mysql* table **test** which only allows 20 entries.
When you have more than 20 (via an *insert* statement) the earliest rows are deleted. The **test**
table looks like this:

    CREATE TABLE `test` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(254) DEFAULT NULL,
      `lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8

This table is on my home computer Hp-envy.

The only file that can be run is *worker.main.php*. That file uses *worker.worker.js* as a
**worker** and that file uses *worker.ajax.php* as a AJAX target to do the actual SQL query.
If you browse the *worker.main.php* you can view all three *worker...* files by clicking on
the button: [<button>View the file
**worker.main.php**, **worker.worker.js** and **worker.ajax.php**</button>](https://www.bartonphillips.com/examples.js/user-test/worker.main.php)

