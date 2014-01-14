<?php
$cs = Yii::app()->getClientScript();
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.21.custom.min.js');
?>

<div class="row">
	<div class="span8 offset2 form well">
		<div class="clear"></div>
		<?  Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.21.custom.min.js'); ?>
		<div class="form">
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'file-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('class'=>'form-horizontal')
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="control-group">
		<?php echo $form->labelEx($model,'dataset_id',array('class'=>'control-label')); ?>
				<div class="controls">
        <?= CHtml::activeDropDownList($model,'dataset_id',CHtml::listData(Dataset::model()->findAll(),'id','identifier')); ?>
		<?php echo $form->error($model,'dataset_id'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'name',array('class'=>'control-label')); ?>
				<div class="controls">
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'location',array('class'=>'control-label')); ?>
				<div class="controls">
		<?php echo $form->textField($model,'location',array('size'=>60,'maxlength'=>200)); ?>
		<?php echo $form->error($model,'location'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'extension',array('class'=>'control-label')); ?>
				<div class="controls">
		<?php echo $form->textField($model,'extension',array('size'=>30,'maxlength'=>30)); ?>
		<?php echo $form->error($model,'extension'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'size',array('class'=>'control-label')); ?>
				<div class="controls">
		<?php echo $form->textField($model,'size'); ?>
		<?php echo $form->error($model,'size'); ?>
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
		<?php echo $form->labelEx($model,'date_stamp',array('class'=>'control-label')); ?>
				<div class="controls">
		<?php echo $form->textField($model,'date_stamp' , array('class' => 'date')); ?>
		<?php echo $form->error($model,'date_stamp'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'format_id',array('class'=>'control-label')); ?>
				<div class="controls">
        <?= CHtml::activeDropDownList($model,'format_id',CHtml::listData(FileFormat::model()->findAll(),'id','name')); ?>
		<?php echo $form->error($model,'format_id'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'type_id',array('class'=>'control-label')); ?>
				<div class="controls">
        <?= CHtml::activeDropDownList($model,'type_id',CHtml::listData(FileType::model()->findAll(),'id','name')); ?>
		<?php echo $form->error($model,'type_id'); ?>
				</div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'code',array('class'=>'control-label')); ?>
				<div class="controls">
		<?php echo $form->textField($model,'code',array('size'=>60,'maxlength'=>64)); ?>
		<?php echo $form->error($model,'code'); ?>
				</div>
	</div>

	<div class="pull-right">
        <a href="/adminFile/admin" class="btn">Cancel</a>
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
	</div>
</div>
<script>
$('.date').datepicker();
</script>
