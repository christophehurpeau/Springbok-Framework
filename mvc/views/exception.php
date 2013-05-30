<!DOCTYPE html>
<html style="margin:0;padding:0">
	<head>
		<meta charset="UTF-8">
	</head>
	<body style="margin:0;padding:0 5px 0">
<h1 style="background:#F5CCCC;color:#B80000;border:1px solid #B80000;margin:1px 0 0;padding:1px 2px;"><?php if($e instanceof HttpException && $e->hasDescription()) echo $e->getDescription(); else echo h($e_className) ?></h1>
<?php /*#if DEV */ HDev::exception($e); HDev::springbokBar(true);/*#/if*/ ?>
	</body>
</html>