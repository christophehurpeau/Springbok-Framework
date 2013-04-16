<?php
if(empty($argv[0])) echo 'Which model ?';
else{
	
	$argv[0]::rebuild();
	
	echo "Tree rebuiled";
}