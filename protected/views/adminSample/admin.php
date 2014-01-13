
<h1>Manage Samples</h1>

<a href="/adminSample/create" class="btn">Create New Sample</a>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'sample-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass'=>'table table-bordered',
	'filter'=>$model,
	'columns'=>array(
		array('name'=> 'species_search', 'value'=>'$data->species->common_name'),
		array('name'=> 'dois_search', 'value'=>'implode(\', \',CHtml::listData($data->datasets,\'id\',\'identifier\'))'),
		's_attrs',
		'code',
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
