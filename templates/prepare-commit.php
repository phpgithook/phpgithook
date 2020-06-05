#!/bin/bash

exec < /dev/tty

<?php echo $phpbin; ?> <?php echo $phpgithookbin; ?> phpgithook:prepare-commit "$1" "$2" "$3" "$PWD"
