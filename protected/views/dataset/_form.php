<?
if(Yii::app()->user->hasFlash('saveSuccess'))
	echo Yii::app()->user->getFlash('saveSuccess');

$cs = Yii::app()->getClientScript();
$cssCoreUrl = $cs->getCoreScriptUrl();
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.21.custom.min.js');
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');

?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'dataset-form',
	'enableAjaxValidation'=>false,
    'htmlOptions'=>array(
        'class'=>'form-horizontal',
        'enctype'=>'multipart/form-data'),
)); ?>
<div class="span12 form well">
	<div class="form-horizontal">
		<p class="note">Fields with <span class="required">*</span> are required.</p>
		<div class="clear"></div>
		<?php echo $form->errorSummary($model); ?>
		<div class="span5">
			<div class="control-group">
				<?php echo $form->labelEx($model,'submitter_id',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->dropDownList($model,'submitter_id',MyHtml::listData(User::model()->findAll(),'id','email')); ?>
					<?php echo $form->error($model,'submitter_id'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $form->labelEx($model,'upload_status',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->dropDownList($model,'upload_status',array('Pending'=>'Pending','Published'=>'Published')); ?>
					<?php echo $form->error($model,'upload_status'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $form->labelEx($model,'types',array('class'=>'control-label')); ?>
				<div class="controls">
					<?
						$datasetTypes = MyHtml::listData(Type::model()->findAll(),'id','name');
						$checkedTypes = MyHtml::listData($model->datasetTypes,'id','id');
						foreach ($datasetTypes as $id => $datasetType) {
							$checkedHtml = in_array($id,$checkedTypes,true) ? 'checked="checked"' : '';
							echo '<input type="checkbox" name="datasettypes['.$id.']" value="1"'.$checkedHtml.'/> '.$datasetType.'<br/>';
						}
					?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $form->labelEx($model,'title',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>300)); ?>
					<?php echo $form->error($model,'title'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'description',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
					<?php echo $form->error($model,'description'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'dataset_size',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model,'dataset_size',array('size'=>60,'maxlength'=>200)); ?> (bytes)
					<?php echo $form->error($model,'dataset_size'); ?>
				</div>
			</div>
		</div>

		<div class="span6">
<?
                        $img_url = $model->image->image('image_upload');
                        $fn = '' ;
                        if($img_url){
                            $fn = explode('/' , $img_url);
                            $fn = end($fn);
                        }
?>
			<? echo ($img_url && $fn !='Images_.png') ? MyHtml::image($img_url, $img_url, array('style'=>'width:100px; margin-left:160px;margin-bottom:10px;')) : ''; ?>
			<div class="control-group">
				<?php echo $form->labelEx($model->image,'Image Upload',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $model->image->imageChooserField('image_upload'); ?>
					<?php echo $form->error($model->image,'image_upload'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $form->labelEx($model->image,'url',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model->image,'url',array('size'=>60,'maxlength'=>200)); ?>
					<?php echo $form->error($model->image,'url'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model->image,'source',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model->image,'source',array('size'=>60,'maxlength'=>200)); ?>
					<?php echo $form->error($model->image,'source'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model->image,'tag',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model->image,'tag',array('size'=>60,'maxlength'=>200)); ?>
					<?php echo $form->error($model->image,'tag'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model->image,'license',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model->image,'license',array('size'=>60,'maxlength'=>200)); ?>
					<?php echo $form->error($model->image,'license'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model->image,'photographer',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model->image,'photographer',array('size'=>60,'maxlength'=>200)); ?>
					<?php echo $form->error($model->image,'photographer'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'identifier',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model,'identifier',array('size'=>32,'maxlength'=>32,
																			'ajax' => array(
																				'type' => 'POST',
																				'url' => array('dataset/checkDOIExist'),
																				'dataType' => 'JSON',
																				'data'=>array('doi'=>'js:$(this).val()'),
																				'success'=>'function(data){
																					if(data.status){
																						$("#Dataset_identifier").addClass("error");
																					}else {
																						$("#Dataset_identifier").removeClass("error");

																					}
																				}',
																			),
																			)); ?>
					<?php echo $form->error($model,'identifier'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'ftp_site',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->textField($model,'ftp_site',array('size'=>60,'maxlength'=>200)); ?>
					<?php echo $form->error($model,'ftp_site'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'publisher',array('class'=>'control-label')); ?>
				<div class="controls">
					<?php echo $form->dropDownList($model,'publisher_id',MyHtml::listData(Publisher::model()->findAll(),'id','name')); ?>
					<?php echo $form->error($model,'publisher_id'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'publication_date',array('class'=>'control-label')); ?>
				<div class="controls">
				<?php echo $form->textField($model,'publication_date',array('class'=>'date')); ?>
				<?php echo $form->error($model,'publication_date'); ?>
				</div>
			</div>

			<div class="control-group">
				<?php echo $form->labelEx($model,'modification_date',array('class'=>'control-label')); ?>
				<div class="controls">
				<?php echo $form->textField($model,'modification_date',array('class'=>'date')); ?>
				<?php echo $form->error($model,'modification_date'); ?>
				</div>
			</div>

		</div>
	</div>
</div>

<div class="span12" style="text-align:center">
	<a href="<?=Yii::app()->createUrl('/dataset/admin')?>" class="btn"/>Cancel</a>
	<?= CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'btn-green')); ?>
        <? if (!$model->isNewRecord && ($model->upload_status != 'Published')) { ?>
	<a href="<?=Yii::app()->createUrl('/dataset/private/identifier/'.$model->identifier)?>" class="btn-green"/>Create/Reset Private URL</a>
        <?if($model->token){?>
        <a href="<?= Yii::app()->createUrl('/dataset/view/id/'.$model->identifier.'/token/'.$model->token) ?>" class="btn-green">Open Private URL</a>
        <?}?>
        <? } ?>
</div>
<?php $this->endWidget(); ?>
<script>
$('.date').datepicker();
</script>
