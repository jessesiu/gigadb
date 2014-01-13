<h2>Create Dataset</h2>
<div class="clear"></div>

<? if(!isset($_SESSION['identifier'])) { ?>
<font class="btn-green-active span1"><?= Yii::t('app' , 'Study')?></font>
<input type="submit" id="author-btn" class="btn nomargin next1" value="Author"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Project"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Link"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="External Link"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Related Doi"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Sample"></input>

<? } else { ?>

<font class="btn-green-active span1"><?= Yii::t('app' , 'Study')?></font>
<a href="/adminDatasetAuthor/create1" class="btn nomargin"><?= Yii::t('app' , 'Author')?></a>
<a href="/adminDatasetProject/create1" class="btn nomargin"><?= Yii::t('app' , 'Project')?></a>
<a href="/adminLink/create1" class="btn nomargin"><?= Yii::t('app' , 'Link')?></a>
<a href="/adminExternalLink/create1" class="btn nomargin"><?= Yii::t('app' , 'ExternalLink')?></a>
<a href="/adminRelation/create1" class="btn nomargin"><?= Yii::t('app' , 'Related Doi')?></a>
<a href="/adminDatasetSample/create1" class="btn nomargin"><?= Yii::t('app' , 'Sample')?></a>

<? if(isset($_SESSION['filecount']) && $_SESSION['filecount']>0) {?>
<a href="/adminFile/create1" class="btn nomargin"><?= Yii::t('app' , 'File')?></a>
<? } ?>

<? } ?>

<? 
    $this->renderPartial('_form1', array('model'=>$model));     
?>


