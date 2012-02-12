<!DOCTYPE html>
<html style="margin:0;padding:0">
	<head>
		<meta charset="UTF-8">
	</head>
	<body style="margin:0;padding:32px 5px 0">
		<h1 style="background:#FFAADD;color:#333;border:1px solid #E00873;margin:1px 0 0;padding:1px 2px">DB : check ?</h1>
		<p><a href="<?php echo $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'&':'?') ?>check=springbokCheckFalse">No</a>
		<a href="<?php echo $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'&':'?') ?>check=springbokCheckTrue">Yes</a></p>
		<?php echo HDev::springbokBar(); ?>
	</body>
</html>