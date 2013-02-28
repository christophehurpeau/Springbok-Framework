<!DOCTYPE html>
<html style="margin:0;padding:0">
	<head>
		<? HHtml::metaCharset() ?>
	</head>
	<body style="margin:0;padding:32px 5px 0">
		<h1 style="background:#6F006F;color:#FFF;border:1px solid #530053;font-size:bold;margin:1px 0 0;padding:2px 3px">DB : operations applied</h1>
		<h2 style="background:#b3779b;color:#1a1116;border:1px solid #4d3342;margin:12px 0 2px;padding:2px 4px">DB : version</h2>
			<ul>
			<?php foreach($versions as $version): ?>
				<li>
					<b><?php echo h($version) ?></b> : <?php echo date('Y-m-d H:i:s',$version) ?>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php if(!empty($error)): ?>
		<div>An error occured : <?php echo h($error->getMessage()) ?></div>
		<div>Query : <?php echo h($errorQuery) ?></div>
		<?php endif; ?>
			
		<a href="<?php echo $_SERVER['REQUEST_URI'] ?>">Continuer</a>
		<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Queries:</h5>
		<?php echo HDev::springbokBar(true); ?>
	</body>
</html>