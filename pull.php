<?php
echo trim(shell_exec('git pull 2>&1 || git pull 2>&1 ; git submodule update 2>&1'));
echo "\n";
echo trim(shell_exec('sudo su www-data -c "php enhance_cli.php 2>&1"'));
echo "\n";