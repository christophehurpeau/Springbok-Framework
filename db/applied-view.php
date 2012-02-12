<!DOCTYPE html>
<html style="margin:0;padding:0">
	<head>
		<meta charset="UTF-8">
	</head>
	<body style="margin:0;padding:32px 5px 0">
		<h1 style="background:#FFAADD;color:#333;border:1px solid #E00873;margin:1px 0 0;padding:1px 2px">DB : operations applied</h1>
		<?php foreach($dbs as $dbName=>$tables): ?>
			<h2 style="background:#b3779b;color:#1a1116;border:1px solid #4d3342;margin:12px 0 2px;padding:2px 4px"><?php echo h($dbName) ?></h2>
			<ul>
			<?php foreach($tables as $tableName=>$operations): ?>
				<li>
					<b><?php echo h($tableName) ?></b>
					<ul>
						<?php foreach($operations as $operation): ?>
						<li><?php echo h($operation) ?></li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
		<a href="<?php echo substr($_SERVER['REQUEST_URI'],0,-strlen('?apply=springbokProcessSchema')) ?>">Retour</a>
		<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Queries:</h5>
		<?php echo HDev::springbokBar(); ?>
	</body>
</html>