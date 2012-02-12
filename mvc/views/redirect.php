<?php $v=new View('REDIRECT','base') ?>
<?php /* DEV */
/*echo '<script type="text/javascript">
//<![CDATA[
'.file_get_contents(CORE.'includes'.DS.'js'.DS.'jquery-1.6.4.min.js').'
//]]>
</script>'; */
/* /DEV */
?>
<h1 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Redirect</h1>
<p>
URL : <?php echo HHtml::link($url,false,array('rel'=>'container')) ?>
</p>
<?php /* DEV */
echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Call Stack:</h5><pre>'.prettyHtmlBackTrace(3).'</pre>';
/* /DEV */ ?>
<?php $v->render();