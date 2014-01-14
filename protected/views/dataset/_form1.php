<?
if (Yii::app()->user->hasFlash('saveSuccess'))
    echo Yii::app()->user->getFlash('saveSuccess');

$cs = Yii::app()->getClientScript();
$cssCoreUrl = $cs->getCoreScriptUrl();
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.21.custom.min.js');
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
?>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'dataset-form',
    'enableAjaxValidation' => false,
    'htmlOptions' => array(
        'class' => 'form-horizontal',
        'enctype' => 'multipart/form-data'),
        ));
?>
<div class="span12 form well">
    <div class="form-horizontal">
        <p class="note">Fields with <span class="required">*</span> are required.</p>
        <div class="clear"></div>
        <?php echo $form->errorSummary($model); ?>
        <div class="span5">
            <div class="control-group">
                <?php echo $form->labelEx($model, 'submitter_id', array('class' => 'control-label')); ?>
                <div class="controls">

                    <?php
                    $email = Yii::app()->user->getEmail();
                    echo CHtml::textField("email", $email, array('size' => 60, 'maxlength' => 300, 'readonly' => "readonly")
                    );
                    ?> 
                </div>
            </div>
            <div class="control-group">


                <?php echo $form->labelEx($model, 'types', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="Select the type of data to be included 
                   in this submission, you may select more than 1. If a 
                   data type is missing please contact us on database@gigasciencejournal.com."></a>
                <div class="controls">
                    <?
                    $datasetTypes = MyHtml::listData(Type::model()->findAll(), 'id', 'name');
                    $checkedTypes = MyHtml::listData($model->datasetTypes, 'id', 'id');
                   // echo print_r($$model->datasetTypes);
//                    echo print_r($checkedTypes);
                    foreach ($datasetTypes as $id => $datasetType) {
                        $checkedHtml = in_array($id, $checkedTypes, true) ? 'checked="checked"' : '';
                        echo '<input type="checkbox" name="datasettypes[' . $id . ']" value="1"' . $checkedHtml . '/> ' . $datasetType . '<br/>';
                    }
                    ?>
                </div>
            </div>
            <div class="control-group">

                <?php echo $form->labelEx($model, 'title', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="This should be a short descriptive title
                   of the dataset to be submitted"></a>
                <div class="controls">
                    <?php echo $form->textField($model, 'title', array('size' => 60, 'maxlength' => 300)); ?>
                    <?php echo $form->error($model, 'title'); ?>
                </div>
            </div>

            <div class="control-group">

                <?php echo CHtml::label('Estimated Dataset Size', '', array('class' => 'control-label'));
                ?>
                <a class="myHint" data-content="The approximate
                   combined size of all the files that you intend to submit"></a>
                   <?php //echo $form->labelEx($model, 'dataset_size', array('class' => 'control-label'));
                   ?>
                <div class="controls">
                    <?php echo $form->textField($model, 'dataset_size', array('size' => 60, 'maxlength' => 200)); ?> (bytes)
                    <?php echo $form->error($model, 'dataset_size'); ?>
                </div>
            </div>




        </div>




        <div class="span6">
            <?
            $img_url = $model->image->image('image_upload');
            $fn = '';
            if ($img_url) {
                $fn = explode('/', $img_url);
                $fn = end($fn);
            }
            ?>

            <div class="control-group">
                <font class="control-label">No image</font>
                <a class="myHint" data-content="check it if you don't want to upload an image"></a>
                <div class="controls">
                    <input id="image-upload" name='no-image' type="checkbox" 
                         <? if( isset($_SESSION['images'])&& $_SESSION['images']=='no-image' )
                             echo 'checked'; ?>
                           style="margin-right:5px"/>
                </div>
            </div>

            <? echo ($img_url && $fn != 'Images_.png') ? MyHtml::image($img_url, $img_url, array('style' => 'width:100px; margin-left:160px;margin-bottom:10px;')) : ''; ?>
            <div class="control-group">

                <?php echo $form->labelEx($model->image, 'Image Upload', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="upload an image from your local computer/network"></a>
                <div class="controls">
                    <!--<input type="radio" id="image-upload" name="test">no image<br>-->

                    <?php echo $model->image->imageChooserField('image_upload', array('class' => 'image')); ?>
                    <?php echo $form->error($model->image, 'image_upload'); ?>
                </div>
            </div>
            <div class="control-group">

                <?php echo $form->labelEx($model->image, 'url', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="if you are using an image
                   that is available online you may insert the URL here"></a>
                <div class="controls">
                    <?php echo $form->textField($model->image, 'url', array('size' => 60, 'maxlength' => 200, 'class' => 'image')); ?>
                    <?php echo $form->error($model->image, 'url'); ?>
                </div>
            </div>


            <div class="control-group">

                <?php echo $form->labelEx($model->image, 'source', array('class' => 'control-label')); ?>
                <a class="myHint" data-content= "from where did you get the image, e.g. wikipedia"></a>

                <div class="controls">

                    <?php echo $form->textField($model->image, 'source', array('size' => 60, 'maxlength' => 200, 'class' => 'image')); ?>
                    <?php echo $form->error($model->image, 'source'); ?>
                </div>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model->image, 'tag', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="A brief descriptive title of the image, 
                   this will be shown to users if they hover over the image."></a>
                <div class="controls">
                    <?php echo $form->textField($model->image, 'tag', array('size' => 60, 'maxlength' => 200, 'class' => 'image')); ?>
                    <?php echo $form->error($model->image, 'tag'); ?>
                </div>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model->image, 'license', array('class' => 'control-label')); ?>
                <a class="myHintLink" data-content="GigaScience database will
                   only use images that are free for others to re-use,
                   primarily this is Creative Commons 0 license (CC0)
                   please see <a target='_blank' href='http://creativecommons.org/about/cc0'>here</a> 
                   for further reading on creative commons licenses."></a>
                <div class="controls">
                    <?php echo $form->textField($model->image, 'license', array('size' => 60, 'maxlength' => 200, 'class' => 'image')); ?>
                    <?php echo $form->error($model->image, 'license'); ?>
                </div>
            </div>

            <div class="control-group">

                <?php echo $form->labelEx($model->image, 'photographer', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="The person(s) that should 
                   be credited for the image"></a>
                <div class="controls">
                    <?php echo $form->textField($model->image, 'photographer', array('size' => 60, 'maxlength' => 200, 'class' => 'image')); ?>
                    <?php echo $form->error($model->image, 'photographer'); ?>
                </div>
            </div>

        </div>
    </div>

    <div class="span10">

        <div class="control-group">

            <?php echo $form->labelEx($model, 'description', array('class' => 'control-label')); ?>
            <a class="myHint" data-content="Please provide a full description of the datatset, this may 
               look like an article abstract giving a brief background of the research and a 
               description of the results to be found in the dataset
               (it should be between 100 and 500 word in length). 
               Please note this text box accepts HTML code tags for formatting,
               so you may use &quot;&lt; br &gt;&quot; for line breaks, &quot;&lt; em &gt;&QUOT; <em>for italics</em> &quot;
               &lt; em /&gt;&quot; 
               and &quot;&lt; b &gt;&quot; <b>for bold</b> &quot;&lt; b/ &gt;&quot;"></a>
            <div class="controls">
                <?php echo $form->textArea($model, 'description', array('rows' => 6, 'cols' => 100, 'style' => 'resize:vertical;width:610px')); ?>
                <?php echo $form->error($model, 'description'); ?>
            </div>
        </div>
    </div>

</div>

<div class="span12" style="text-align:center">
    <a href="<?= Yii::app()->createUrl('/dataset/cancel') ?>" class="btn"/>Cancel</a>
    <?= CHtml::submitButton('Next', array('class' => 'btn-green', 'id' => 'next-btn')); ?>
</div>
<?php $this->endWidget(); ?>
<script>
    $('.date').datepicker();


    $(".next1").click(function() {
        $("#next-btn").click();
    });

    $(".myHint").popover();

    $(".myHintLink").popover({trigger: 'manual'}).hover(function(e) {
        $(this).popover('show');
        e.preventDefault();
    });


    $('.myHintLink').on('mouseleave', function() {
        var v = $(this);
        setTimeout(
                function() {
                    v.popover('hide');
                }, 2000);
    });

    $(function() {
        $('#image-upload').click(function() {
            if ($(this).is(':checked')) {
                $('.image').attr('disabled', true);
            } else {
                $('.image').attr('disabled', false);
            }
        });
    });
    
    function disableImage(){     
//        alert('here');
         if ($('#image-upload').is(':checked')) {
                $('.image').attr('disabled', true);
         }
    }
        
    window.onload = disableImage;

</script>

