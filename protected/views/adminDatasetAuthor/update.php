<?php
$this->breadcrumbs=array(
	'Dataset Authors'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List DatasetAuthor', 'url'=>array('index')),
	array('label'=>'Create DatasetAuthor', 'url'=>array('create')),
	array('label'=>'View DatasetAuthor', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage DatasetAuthor', 'url'=>array('admin')),
);
?>

<h1>Update DatasetAuthor <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>