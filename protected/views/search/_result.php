<?php
$dataset_result=$search_result['dataset_result'];

$file_result=$search_result['file_result'];
$data=$dataset_result->getData();

$fsort=$file_result->getSort();
$dsort=$dataset_result->getSort();

$fsort_map = array(
    'dataset.identifier' => 'span2 sorted',
    'name' => 'sorted',
    'code' => 'span2 sorted',
    'type_id' => 'sorted',
    'format_id' => 'span3 sorted',
    'size' => 'sorted',
    'date_stamp' => 'span2 sorted',
);

foreach ($fsort->directions as $key => $value) {
    if (!array_key_exists($key, $fsort_map)) {
        continue;
    }
    $direction = ($value == 1) ? '-down' : '-up';
    $fsort_map[$key] .= $direction;
}

$dsort_map = array(
    'identifier' => 'span2 sorted',
    'title' => 'span6 sorted',
    'publication_date' => 'sorted',
    'modification_date' => 'sorted',
);

foreach ($dsort->directions as $key => $value) {
    if (!array_key_exists($key, $dsort_map)) {
        continue;
    }
    $direction = ($value == 1) ? '-down' : '-up';
    $dsort_map[$key] .= $direction;
}

?>

<ul class="nav nav-tabs" id="myTab">
<li class="active"><a href="#result_dataset" rel="#dataset_filter"><?= Yii::t('app' , 'Dataset')?></a></li>
<li><a href="#result_files" rel="#file_filter"><?=Yii::t('app' , 'File')?></a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="result_dataset">
        <table class="table table-bordered" id ="list">
        <tr>
            <th class="<?= $dsort_map['identifier'] ?>">
                <?= $dsort->link('identifier'); ?>
            </th>
            <th class="<?= $dsort_map['title'] ?>">
                <?= $dsort->link('title')?>
            </th>
            <th class="sorted"><?=Yii::t('app' , 'Common Name')?></th>
            <th class="sorted"><?=Yii::t('app' , 'Dataset Type')?></th>
            <th class="<?= $dsort_map['publication_date'] ?>">
                <?= $dsort->link('publication_date'); ?>
            </th>
            <th class="<?= $dsort_map['modification_date'] ?>">
                <?= $dsort->link('modification_date'); ?>
            </th>
                <th><?=Yii::t('app' , 'Hide Dataset')?></th>
        </tr>

        <?php
        for($i=0;$i<$dataset_result->itemCount;$i++){
            $class=$i%2==0?'even':'odd'; ?>
            <tr class="<?php echo $class; ?>">
                <td class="content-popup" data-content="<? echo MyHtml::encode($data[$i]->description);?>"> <? echo MyHtml::link("10.5524/".$data[$i]->identifier,"/dataset/".$data[$i]->identifier);?> </td>
                <td class="left content-popup" data-content="<? echo MyHtml::encode($data[$i]->description);?>"><? echo $data[$i]->title; ?></td>
                <td><? echo $data[$i]->commonNames ;?> </td>
                <td >
                    <? foreach( $data[$i]->datasetTypes as $type ){?>
                        <span class="content-popup" data-content="<? echo MyHtml::encode($type->description);?>"><?=$type->name?></span>
                        <!--a class="hint" title="<?= $type->description ?>"></a-->
                    <?}?>
                </td>
		    <td><? echo MyHtml::encode(strftime('%d-%m-%Y' , strtotime($data[$i]->publication_date))); ?></td>
                <td><? echo MyHtml::encode(strftime('%d-%m-%Y' , strtotime($data[$i]->modification_date))); ?> </td>
                <td><? echo MyHtml::link(Yii::t('app' , "Hide"), $model->getParams($data[$i]->id) ,array('class'=>'btn btn_hide')) ; ?></td>
            </tr>
       <? }
        ?>
        </table>
        <?php
            $pagination = $dataset_result->getPagination();
            $this->widget('CLinkPager', array(
                'pages' => $pagination,
                'header'=>'',
                'cssFile' => false,
            ));
        ?>

        <?= ($exclude) ? MyHtml::link(Yii::t('app' , "Show Hidden Datasets"), $model->getParams() ,array('class'=>'btn btn_hide')) : '' ?>
    </div>

    <div class="tab-pane" id="result_files">
        <table class="table table-bordered" id="list_files">
            <thead>
                <tr>
                    <? foreach ($fsort_map as $column => $css_class) { ?>
                        <th class="<?= $css_class ?>">
                            <?= $fsort->link($column); ?>
                        </th>
                    <? } ?>
                    <th class="span2"></th>
                </tr>
            </thead>
            <?

            foreach ($file_result->getData() as $key=>$file){


            ?>
            <tr>
                <td class="left content-popup"  ><? echo MyHtml::link("10.5524/".$file->dataset->identifier,"/dataset/".$file->dataset->identifier); ?></td>
                <td class="left content-popup" data-content="<?= MyHtml::encode($file->description) ?>" ><p class='filename'><? echo $file->name;  ?></p></td>
                <td class="left span2"><? echo $file->code;  ?></td>
                <td class="left"><? echo $file->type->name;  ?></td>
                <td><span class='content-popup' data-content="<?= MyHtml::encode($file->format->description) ?>"><? echo $file->format->name;  ?></span> <!--a class="hint" title="<? echo $file->format->description; ?>"></a--></td>
                <td> <? echo $file->bytesToSize(0); ?> </td>
                <td> <? echo strftime('%d-%m-%Y' , strtotime($file->date_stamp) ); ?> </td>
                <td> <? echo MyHtml::link("",$file->location,array('target'=>'_blank' , 'class' => 'download-btn')); ?> </td>
            </tr>
            <? }
            ?>
        </table>
        <?php
            $pagination = $file_result->getPagination();

            $this->widget('CLinkPager', array(
                'pages' => $pagination,
                'header'=>'',
                'cssFile' => false,
            ));


        ?>
    </div>
</div>

<script>

// $('.btn_hide').click(function () {

//     $(this).parents().find("tr").has(this).hide();
// });
</script>

