#!/bin/bash

exec < /dev/tty

<?php echo $phpbin; ?> <?php echo $phpgithookbin; ?> phpgithook:hook:post-commit "$PWD"
