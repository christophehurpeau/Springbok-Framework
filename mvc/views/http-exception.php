<?php /* include_once CORE.'mvc/views/View.php'; $v=new AjaxContentView($title) */ ?>
<!DOCTYPE html>
<html style="margin:0;padding:0">
	<head>
		<meta charset="UTF-8">
	</head>
	<body style="margin:0;padding:32px 5px 0">
<?php /* DEV */
echo '<script type="text/javascript">
//<![CDATA[
'.file_get_contents(dirname(CORE).'/includes/js/jquery-1.7.2.min.js').'
//]]>
</script>';
/* /DEV */
?>
<h1 style="background:#F5CCCC;color:#B80000;border:1px solid #B80000;margin:1px 0 0;padding:1px 2px;"><?php echo h($title) ?></h1>
<p><?php echo h($descr) ?></p>
<?php /* DEV */ HDev::exception($e_message,$e_file,$e_line,$e_trace); HDev::springbokBar();/* /DEV */ ?>
	</body>
</html>
