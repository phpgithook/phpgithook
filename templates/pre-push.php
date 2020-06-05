#!/bin/bash

exec < /dev/tty

<?php echo $phpbin; ?> <?php echo $phpgithookbin; ?> phpgithook:hook:pre-push "$1" "$2"
