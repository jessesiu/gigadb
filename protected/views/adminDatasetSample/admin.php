<h1>Manage Dataset - Samples</h1>

<a href="/adminDatasetSample/create" class="btn">Add a Sample to a Dataset</a>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'dataset-sample-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass'=>'table table-bordered',
	'filter'=>$model,
	'columns'=>array(
		array('name'=> 'doi_search', 'value'=>'$data->dataset->identifier'),
		'sample_id',
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
