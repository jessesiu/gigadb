
<h2>File details</h2>
<div class="clear"></div>

<a href="/dataset/create1" class="btn span1"><?= Yii::t('app' , 'Study')?></a>
<a href="/adminDatasetAuthor/create1" class="btn nomargin"><?= Yii::t('app' , 'Author')?></a>

<a href="/adminDatasetProject/create1" class="btn nomargin"><?= Yii::t('app' , 'Project')?></a>
<a href="/adminLink/create1" class="btn nomargin"><?= Yii::t('app' , 'Link')?></a>
<a href="/adminExternalLink/create1" class="btn nomargin"><?= Yii::t('app' , 'ExternalLink')?></a>
<a href="/adminRelation/create1" class="btn nomargin"><?= Yii::t('app' , 'Related Doi')?></a>
<a href="/adminDatasetSample/create1" class="btn nomargin"><?= Yii::t('app' , 'Sample')?></a>
<input type="submit" value="File" class="btn-green-active nomargin"></input>

<?
$count = count($files);
if($count>0)
     echo $this->renderPartial('_form1', array('files'=>$files,'identifier'=>$identifier,
         'samples_data'=>$samples_data));
else{
    
?>
<div class="span12 form well">
    <div class="form-horizontal">
        <div class="form overflow">
             <p>You can update the files when the administrator upload your files.</p>
             
               <div class="span12" style="text-align:center">                  
                <a href="/dataset/submit" class="btn-green">Submit</a>
            </div>
        </div>
    </div>
</div>

<?
}
?>

