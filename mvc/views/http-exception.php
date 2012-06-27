<?php include_once CORE.'mvc/views/View.php'; $v=new AjaxContentView($title) ?>
<h1 style="background:#F5CCCC;color:#B80000;border:1px solid #B80000;margin:1px 0 0;padding:1px 2px;"><?php echo h($title) ?></h1>
<p><?php echo h($descr) ?></p>
<?php $v->render(); ?>
