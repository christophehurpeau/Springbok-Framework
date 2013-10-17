<?php

if(!isset($folderTests)) $folderTests=APP.'tests/';
if(!file_exists($folderTests)){ echo 'No tests'; exit(0); }
$t=microtime(true);
$tests=new RecursiveDirectoryIterator($folderTests,FilesystemIterator::SKIP_DOTS); $l=strlen($folderTests); $total=$totalFailed=0;
UPhp::recursive(function($callback,$tests) use($l,&$total,&$totalFailed){
	foreach($tests as $path=>$file){
		if($file->isDir())
			$callback($callback,new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS));
		else{
			echo cliColor(substr($path,$l),CliColors::white)."\n";
			$results=STest::runFile($path);
			$stats=STest::cliDisplay($results);
			$total+=$stats['total'];
			$totalFailed+=$stats['failed'];
		}
	}
},$tests);

$t=microtime(true) - $t;
echo "\n";
if($total===0){ echo 'No tests'; exit(0); }
else if($totalFailed===0){ echo cliColor('OK',CliColors::green).' '.$total.'/'.$total.' in '.$t.' ms'; exit(0); }
else{ echo cliColor('FAILED',CliColors::lightRed).' '.($total-$totalFailed).'/'.$total.' in '.$t.' ms'; exit(1); }
