
<h1>Manage Files</h1>

<a href="/adminFile/create" class="btn">Create New File</a>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'file-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass'=>'table table-bordered',
	'filter'=>$model,
	'columns'=>array(
		array('name'=> 'doi_search', 'value'=>'$data->dataset->identifier'),
		'code',
		array(
            'name' => 'name',
            'value'=>'$data->name',
            'htmlOptions' => array('style' => 'width:20px;'),
        ),
		//'location',
		//'extension',

		'date_stamp',
		array('name'=> 'format_search', 'value'=>'$data->format->name'),
		array('name'=> 'type_search', 'value'=>'$data->type->name'),

		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
