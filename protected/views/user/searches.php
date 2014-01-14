<table class="table table-bordered">
	<tr>
	<th colspan="4"><?=Yii::t('app' , 'Your Saved Searches')?></th>
	</tr>
	<tr>
	<th><?=Yii::t('app' , 'Keywords')?></th>
	<th><?=Yii::t('app' , 'Criteria')?></th>
	<th><?=Yii::t('app' , 'Load')?></th>
	<th><?=Yii::t('app' , 'Delete')?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($searchRecord as $search) {
	    $class=$i%2==0?'even':'odd';
	    $i++; ?>
	    <tr class="<?php echo $class; ?>" id="search_line_<? echo $search->id ; ?>">
	        <td><?= $search->name ?></td>
	        <td><?= $search->convertCriteria() ?></td>
	        <td><a href="/search/redirect/id/<?=$search->id; ?>" target="_blank" title="<?Yii::t('app' ,"Perform this search again")?>" class="loadsearch"><?=Yii::t('app' , 'Load')?></a></td>
	        <td><? echo MyHtml::ajaxLink(Yii::t('app' , 'Delete'),
							        	array('search/delete'),
							        	array(
							        		'type' => 'POST',
							        		'dataType' => 'json',
							        		'success' => "function( data )
										                  {

										                    if(data.status=='success'){
										                    	$('#search_line_'+data.id).hide();
										                    }
										                  }",
										     'data' => array( 'id' => $search->id ),



								        )
		        ); ?></td>
	    </tr>
	<? } ?>
</table>
<script>
$(".loadsearch").tooltip();
</script>
</script>
