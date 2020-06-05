#!/bin/bash

exec < /dev/tty

<?php echo $phpbin; ?> <?php echo $phpgithookbin; ?> phpgithook:hook:commit-msg "$1" "$PWD"
