#!/bin/sh
cd /my.websites/cod2demo.ultrastats.org/contrib/
php ../admin/parser-shell.php fullupdate 1 password
php ../admin/parser-shell.php runtotalstats
