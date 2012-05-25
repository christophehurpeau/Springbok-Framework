<!DOCTYPE html>
<html style="margin:0;padding:0">
	<head>
		<meta charset="UTF-8">
	</head>
	<body style="margin:0;padding:32px 5px 0">
<?php 
echo '<script type="text/javascript">
//<![CDATA[
'.file_get_contents(dirname(CORE).'/includes/js/jquery-1.7.2.min.js').'
//]]>
</script>';
?>
		<h1 style="background:#6F006F;color:#FFF;border:1px solid #530053;font-size:bold;margin:1px 0 0;padding:2px 3px">DB : check ?</h1>
		<p><a href="<?php echo $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'&':'?') ?>check=springbokCheckFalse">No</a>
		<a href="<?php echo $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'&':'?') ?>check=springbokCheckTrue">Yes</a></p>
		<?php echo HDev::springbokBar(); ?>
	</body>
</html>