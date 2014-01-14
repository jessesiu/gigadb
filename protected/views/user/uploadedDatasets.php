<div class="tab-pane active" id="result_dataset">
    <table class="table table-bordered" id ="list">
    <tr>
    <th colspan="7"><?=Yii::t('app' , 'Your Submitted Datasets')?></th>
    </tr>
    <tr>
    <th class="span2"><?=Yii::t('app' , 'DOI')?></th>
    <th class="span6"><?=Yii::t('app' , 'Title')?></th>
    <th class="span6"><?=Yii::t('app' , 'Common Name')?></th>
    <th class="span2"><?=Yii::t('app' , 'Dataset Type')?></th>
    <th class="span2"><?=Yii::t('app' , 'Status')?></th>
    <th class="span2"><?=Yii::t('app' , 'Publication Date')?></th>
    <th class="span2"><?=Yii::t('app' , 'Modification Date')?></th>
    </tr>

    <?php
    for($i=0 ; $i < count($uploadedDatasets) ; $i++){
        $class=$i%2==0?'even':'odd';
        $data = $uploadedDatasets;
    ?>
        <tr class="<?php echo $class; ?>">
            <td class="content-popup" data-content="<? echo MyHtml::encode($data[$i]->description);?>"> <? echo MyHtml::link("10.5524/".$data[$i]->identifier,"/dataset/".$data[$i]->identifier,array('target'=>'_blank'));?> </td>
            <td class="left content-popup" data-content="<? echo MyHtml::encode($data[$i]->description);?>"><? echo $data[$i]->title; ?> </td>
            <td><? echo $data[$i]->commonNames ;?> </td>
            <td >
                <? foreach( $data[$i]->datasetTypes as $type ){?>
                    <?=$type->name?>
                    <a class="hint" title="<?= $type->description ?>"></a>
                <?}?>
            </td>
            <td><?= MyHtml::encode($data[$i]->upload_status) ?></td>
            <td><? echo MyHtml::encode($data[$i]->publication_date); ?> </td>
            <td><? echo MyHtml::encode($data[$i]->modification_date); ?> </td>
        </tr>
    <? } ?>
    </table>
</div>
<script>
$(".hint").tooltip({'placement':'left'});
</script>
