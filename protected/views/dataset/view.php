<?
Yii::app()->clientScript->registerScriptFile('/js/jquery.tablesorter.js');

$title= strlen($model->title)>100?strip_tags(substr($model->title, 0,100))." ...":strip_tags($model->title);
$this->pageTitle="GigaDB Dataset - DOI 10.5524/".$model->identifier." - ".$title;
?>
<? $this->renderPartial('/search/_form', array('model' => $form, 'dataset' => $dataset, 'previous_doi' => $previous_doi, 'next_doi' => $next_doi, 'search_result' => null)); ?>
<div class="row">
    <div class="span12"><p><?= Yii::t('app' , 'Data released on')?> <?= strftime("%B %d, %Y",strtotime($model->publication_date)) ?></p></div>
</div>
<div class="row">
    <div class="span12">
        <div class="data-img" style="float:right;width:220px;margin-left:20px;margin-bottom:20px;">
            <h3><? echo MyHtml::encode(implode(", ", $model->getDatasetTypes()));?></h3>
            <? if (isset($model->image)) {
                $url = $model->getImageUrl();
                /*
                if (substr($model->image->url, 0,6) != 'http://' && substr($model->image->url, 0,5) != 'ftp://') {
                    $url = $model->image->url;
            }*/
                echo '<a href="'.$url.'">';
                echo MyHtml::image($url? $url : $model->image->image('image_upload'),
                                $model->image->image('image_upload'),
                                array('class'=>'image-hint','title'=>'<ul style="text-align:left;"><li>'.$model->image->tag.'</li><li>'.'License: '.$model->image->license.'</li><li>'.'Source: '.$model->image->source.'</li><li>'.'Photographer: '.$model->image->photographer.'</li></ul>'));
                echo '</a>';
            } else echo''; ?>
        </div>
        <h3 class='dataset-title'><?echo $model->title; ?></h3>
        <? if (count($model->authors) > 0) { ?>
        <p><h4>
            <?  $i = 0;
                foreach( $model->authors as $key => $author){
                ?>
                    <?
                    if (++$i < count($model->authors)) echo $author->name.';'; else echo $author->name.' ';
                    ?>
                <? }
            ?>
            (<?=substr($model->publication_date,0,4)?>): <?echo $model->title.' '.$model->publisher->name.'. '; ?>
             <a href="http://dx.doi.org/10.5524/<? echo $model->identifier; ?>">http://dx.doi.org/10.5524/<? echo $model->identifier; ?></a>
             <a title="Export to Reference Manager/EndNote" href="http://data.datacite.org/application/x-research-info-systems/10.5524/<? echo $model->identifier; ?>"><span class="citation-button">RIS</span></a>              <a title="Export to BibTeX" href="http://data.datacite.org/application/x-bibtex/10.5524/<? echo $model->identifier; ?>"><span class="citation-button">BibTeX</span></a>   
             <a title="Export to Text" href="http://data.datacite.org/application/x-datacite+text/10.5524/<? echo $model->identifier; ?>"><span class="citation-button">Text</span></a>
        </h4></p>
        <? } ?>
        <p><?echo $model->description; ?> </p>
        
                <div class="row">   
<? if (Yii::app()->user->isGuest) { ?>        
            
            <? echo MyHtml::link("Contact Submitter", "javascript: void(0)",array('class' => 'span2 btn-grey', 'title' => 'Please Login to contact submitter','disabled'=>'disabled')) ?>         
        <? } else { ?>
            <? echo MyHtml::link("Contact Submitter", 'mailto:' . $email, array('class' => 'span2 btn-green')) ?>  
        <? } ?>
        </div>
        <div class="clear"></div>
<?/*h4>In accordance with our <a href="/site/term">terms of use</a>, please cite this dataset as:</h4>
        <? if (count($model->authors) > 0) { ?>
        <p>
            <?  $i = 0;
                foreach( $model->authors as $key => $author){
                ?>
                    <?
                    if (++$i < count($model->authors)) echo $author->name.';'; else echo $author->name.' ';
                    ?>
                <? }
            ?>
            (<?=substr($model->publication_date,0,4)?>): <?echo $model->title.' '.$model->publisher->name.'. '; ?><a href="http://dx.doi.org/10.5524/<? echo $model->identifier; ?>">http://dx.doi.org/10.5524/<? echo $model->identifier; ?></a>

        </p>
        <? }*/ ?>
        <? if (count($model->manuscripts) > 0) { ?>
        <h4><?= Yii::t('app' , 'Related manuscripts:')?></h4>
        <p>
            <? foreach ($model->manuscripts as $key=>$manuscript){
                echo 'doi:' . MyHtml::link($manuscript->identifier, $manuscript->getDOILink());
                if ($manuscript->pmid){
                    $pubmed = MyHtml::link($manuscript->pmid , "http://www.ncbi.nlm.nih.gov/pubmed/" . $manuscript->pmid);
                    echo " (PubMed: $pubmed)";
                }
                echo "<br/>";
            }
            ?>
        </p>
        <? } ?>
        <? if (count($model->relations) > 0) { ?>
        <h4><?= Yii::t('app' , 'Related datasets:')?></h4>
        <p>
            <? foreach ($model->relations as $key=>$relation){
                echo "doi:" . MyHtml::link("10.5524/". $model->identifier, '/dataset/'.$model->identifier) ." " . $relation->relationship . " " .'doi:' . MyHtml::link("10.5524/".$relation->related_doi, '/dataset/'.$relation->related_doi);
                echo "<br/>";
            }
            ?>
        </p>
        <? } ?>
        <? if (count($model->externalLinks) > 0) { ?>
        <p>
            <?  $types = array();

                foreach ($model->externalLinks as $key=>$externalLink){
                    $types[$externalLink->externalLinkType->name] = 1;
                }

                foreach ($types as $typeName => $value) {
                    $typeNameLabel = preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$typeName);
                    $typeNameLabel = preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $typeNameLabel);
                    $typeNameLabel = trim($typeNameLabel);

                    echo "<h4>$typeNameLabel:</h4>";
                    foreach ($model->externalLinks as $key=>$externalLink){
                        if ($externalLink->externalLinkType->name == $typeName) {
                            echo '<p>'. MyHtml::link($externalLink->url, $externalLink->url) . '</p>';
                        }
                    }
                }
            ?>
        </p>
        <? } ?>
        <? if (count($model->links) > 0) { ?>

            <?
            $primary_links = array();
            $secondary_links = array();

            foreach ($model->links as $key=>$link) {
                if ($link->is_primary) {
                    $primary_links[] = $link;
                }
                if (!$link->is_primary) {
                    $secondary_links[] = $link;
                }
            }
            ?>

            <? if (!empty($primary_links)) { ?>
            <h4><?=Yii::t('app' , 'Accessions (data included in GigaDB):')?></h4>
                <p>
                    <? foreach ($primary_links as $link) { ?>
                        <?
                        $tokens = explode(':', $link->link);
                        $name = $tokens[0];
                        $code = $tokens[1];
                        ?>
                        <?= $name ?>:
                        <?= MyHtml::link($code, $link->getLink(), array('target'=>'_blank')); ?>
                        <br/>
                    <? } ?>
                </p>
            <? } ?>

            <? if (!empty($secondary_links)) { ?>
                <h4><?=Yii::t('app' , 'Accessions (data not in GigaDB):')?></h4>
                <p>
                    <? foreach ($secondary_links as $link) { ?>
                        <?
                        $tokens = explode(':', $link->link);
                        $name = $tokens[0];
                        $code = $tokens[1];
                        ?>
                        <? if ($name != 'http') { ?>
                            <?= $name ?>:
                            <?= MyHtml::link($code, $link->getLink(), array('target'=>'_blank')); ?>
                        <? }else { ?>
                            <?= MyHtml::link($link->link , $link->link,array('target'=>'_blank')); ?>
                        <? } ?>
                        <br/>
                    <? } ?>
                </p>
            <? } ?>

        <? } ?>
        <? if (count($model->projects) > 0) { ?>
        <h4><?=Yii::t('app' , 'Projects:')?></h4>
        <p>
            <? foreach ($model->projects as $key=>$project){
                if ($project->image_location)
                    echo "<a href='$project->url'><img src='$project->image_location' /></a>";
                else
                    echo MyHtml::link($project->name, $project->url);

                echo "<br/>";
            }
            ?>
        </p>
        <? } ?>
    </div>
</div>


<div class="row">
    <div class="span12">
        <?if($samples->getData()){?>
        <h4><?=Yii::t('app' , 'Samples:')?></h4>
        <table class="table table-bordered" id='sample-table'>
            <thead>
            <th class="span2"><a href='#sample-table'><?=Yii::t('app' , 'Sample ID')?></a></th>
            <th class="span2"><a href='#sample-table'><?=Yii::t('app' , 'Taxonomic ID')?></a></th>
            <th class="span2"><a href='#sample-table'><?=Yii::t('app' , 'Common name')?></a></th>
            <th class="span2"><a href='#sample-table'><?=Yii::t('app' , 'Genbank name')?></a></th>
            <th class="span2"><a href='#sample-table'><?=Yii::t('app' , 'Scientific name')?></a></th>
            <th class="span6"><a href='#sample-table'><?=Yii::t('app' , 'Sample attributes')?></a></th>
            </thead>
            <? foreach ($samples->getData() as  $sample) {
                $samplelink = $samplecode = $sample->code;
                //if(strstr($samplecode , 'SAMPLE:')){
                    $samplecode = explode(":" , $samplecode);
                    if(count($samplecode) > 1)
                        $samplelink = MyHtml::link($samplecode[1],"http://www.ncbi.nlm.nih.gov/biosample?term=".$samplecode[1]);
                    //$samplelink = 'SAMPLE:' . $samplelink;
                //}
                ?>
                <tr>
                    <td class="left"><?=$samplelink?></td>
                    <td><?= MyHtml::link($sample->species->tax_id, Species::getTaxLink($sample->species->tax_id)) ?></td>
                    <td><?= MyHtml::encode($sample->species->common_name) ?></td>
                    <td><?= MyHtml::encode($sample->species->genbank_name) ?></td>
                    <td><?= MyHtml::encode($sample->species->scientific_name) ?></td>
                    <td class="left">
<?
                    $s_attrs = Sample::model()->embedDiseaseLinkInAttributes($sample->s_attrs);
                    Yii::log("{$sample->s_attrs}    .... $s_attrs" , 'debug');
                    $s_attrs = Sample::model()->sampleAttributesToArray($s_attrs);
                    $print_sa = '';
                    foreach($s_attrs as $key=>$value){
                        $print_sa .= "$key=\"$value\"<br>";
                    }
                    //$s_attrs = implode('<br>' , Sample::model()->sampleAttributesToArray($s_attrs));
                    //$s_attrs = http_build_query($s_attrs ,'' , '<br>');
                    //$s_attrs = urldecode($s_attrs);
                    echo $print_sa;

 ?>
                    </td>
                </tr>

            <? }?>

        </table>
<?php

            $pagination = $samples->getPagination();
            $this->widget('CLinkPager', array(
                'pages' => $pagination,
                'header'=>'',
                'cssFile' => false,
            ));
        }
        ?>
        <div class="clear"></div>

<!--script>
function getCookie(key) {
    var i,x,y,ARRcookies=document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++) {
      x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
      y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
      x=x.replace(/^\s+|\s+$/g,"");
      if (x==key) {
        return unescape(y);
      }
    }
}
function setCookie(column){
    order = 0;
    if (getCookie("file_sort_column") == column){
        order = (getCookie("file_sort_order") == 0) ? 1 : 0;
    }

    document.cookie="file_sort_column" + "=" + escape(column) + "; path=/";
    document.cookie="file_sort_order" + "=" + order + "; path=/";

//    console.log('Sort Column set to: ' + column);
//    console.log('Sort Order set to: ' + order);
}
</script-->

<?
                $aspera = null;
                if($model->ftp_site){
                    $aspera = strstr( $model->ftp_site , 'pub/');
                    if($aspera)
                        $aspera = 'http://aspera.gigadb.org/?B=' . $aspera;
                }

?>
    <h4><?=Yii::t('app' , 'Files')?> <?= MyHtml::link(Yii::t('app','(FTP site)'),$model->ftp_site,array('target'=>'_blank'))?> <?=($aspera) ? MyHtml::link(Yii::t('app' ,'(Aspera)') , $aspera , array('target'=>'_blank')) : ''?>:<span style='color:#666666;font-size:10px;font-weight:normal;margin-left:5px;'><?=Yii::t('app' , 'Aspera user name: gigadb , password: gigadb')?></span>
    
      <?php
            echo CHtml::link('Table Settings', "", // the link for open the dialog
                    array(
                'style' => 'cursor: pointer; text-decoration: underline;',
                'onclick' => "{ $('#dialogDisplay').dialog('open');}"));
            ?>

            <?php
            $this->beginWidget('zii.widgets.jui.CJuiDialog', array(// the dialog
                'id' => 'dialogDisplay',
                'options' => array(
                    'title' => 'Display Setting',
                    'autoOpen' => false,
                    'modal' => true,
                    'width' => 300,
                    'height' => 200,
                    'buttons' => array(
                        array('text' => 'Submit', 'click' => 'js:function(){ document.myform.submit();}'),
                        array('text' => 'Cancel', 'click' => 'js:function(){$(this).dialog("close");}')),
                ),
            ));
            ?>
            <div class="divForForm">
                <form name="myform" action="/dataset/resetPageSize" method="post">  
                    Items per page:
                    <select name="filePageSize" class="selectPageSize">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>                 
                    </select>
                    <input type="hidden" name="url" value="<? echo Yii::app()->request->requestUri; ?>" />

                </form>

            </div>    

                <?php $this->endWidget(); ?>

    </h4>
        
    
    <table class="table table-bordered tablesorter" id="file-table">
            <!--tr-->
            <thead>
                <!--th class="span2"><a href='#' onClick="setCookie('dataset.identifier')">DOI</a></th-->
<?
                //TODO: This part is also dupicated
                $fsort = $files->getSort();
                $fsort_map = array(
                    'name' => 'span5',
                    'code' => 'span5',
                    'type_id' => 'span2',
                    'format_id' => 'span2',
                    'size' => 'span1',
                    'date_stamp' => 'span3',
                );

                foreach ($fsort->directions as $key => $value) {
                    if (!array_key_exists($key, $fsort_map)) {
                        continue;
                    }
                    $direction = ($value == 1) ? ' sorted-down' : ' sorted-up';
                    $fsort_map[$key] .= $direction;
                }
?>
    <?/*<th class="span5"><a href='#' onClick="setCookie('name')">File Name</a></th--><th class="span5"><?=$fsort->link('name')?></th>
                <th class="span5">Sample ID</th>
                <th class="span2"><a href='#' onClick="setCookie('type_id')">File Type</a></th>
                <th class="span2"><a href='#' onClick="setCookie('format_id')">File Format</a></th>
                <th class="span1"><a href='#' onClick="setCookie('size')">Size</a></th>
                <th class="span3"><a href='#' onClick="setCookie('date_stamp')">Release Date</a></th>*/
    foreach($fsort_map as $column => $css){?>
                <th class="<?=$css?>"><?=$fsort->link($column)?></th>
<?}
?>
                <th class="span2"></th>
            </thead>
            <!--/tr-->
            <?
            $pageSize = isset(Yii::app()->request->cookies['filePageSize']) ?
                    Yii::app()->request->cookies['filePageSize']->value : 10;

            $files->getPagination()->pageSize = $pageSize;

            foreach ($files->getData() as $file) {
                $samplelink = $samplecode = $file->code;
                if(strstr($samplecode , 'SAMPLE:')){
                    $samplecode = explode(":" , $samplecode);
                    $samplelink = MyHtml::link($samplecode[1],"http://www.ncbi.nlm.nih.gov/biosample?term=".$samplecode[1]);
                    $samplelink = 'SAMPLE:' . $samplelink;
                }

                    ?>
                <tr>
                    <!--td><?= MyHtml::link("10.5524/".$model->identifier,"/dataset/".$model->identifier); ?></td-->
                    <td class="left" title="<?= $file->description  ?>"><p class='filename'><?= MyHtml::link($file->name ,$file->location , array('target'=>'_blank'));  ?> </p></td>
                    <td class="left"><?= $samplelink ?></td>
                    <td class="left"><?= $file->type->name  ?></td>
                    <td><span class='content-popup' data-content='<?=$file->format->description?>'><?= MyHtml::encode($file->format->name)?></span></td>
                    <td><span style="display:none"><?= File::staticGetSizeType($file->size).' '.strlen($file->size).' '.$file->size?></span><?= MyHtml::encode(File::staticBytesToSize($file->size))?></td>
                    <td><?= MyHtml::encode($file->date_stamp);?></td>
                    <td> <? echo MyHtml::link("",$file->location,array('target'=>'_blank' , 'class' => 'download-btn')); ?> </td>
                </tr>
            <?} ?>

        </table>
        <?php
            $pagination = $files->getPagination();
            $this->widget('CLinkPager', array(
                'pages' => $pagination,
                'header'=>'',
                'cssFile' => false,
            ));
        ?>
    </div>
</div>


<div class="clear"></div>
<div class="row">
    <div class="pull-right">
        <div class="count-btn" id="facebook-share-btn">
            <script>(function(d){
                var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
                    js = d.createElement('script'); js.id = id; js.async = true;
                js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
                d.getElementsByTagName('head')[0].appendChild(js);
                }(document));
            </script>
          <fb:share-button href="<?=Yii::app()->createUrl('dataset/'.$model->identifier)?>" type="button_count">
          </fb:share-button>
        </div>
        <div class="count-btn">
            <div class="g-plus" data-action="share" data-annotation="bubble"></div>
        </div>
        <div class="count-btn">
            <a href="https://twitter.com/share" class="twitter-share-button" data-via="GigaScience">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
            </script>
        </div>
<? /*
        <div class="pull-right">
        <span id="followBtn">Follow on Sina </span>
        <?  Yii::app()->clientScript->registerScriptFile('http://tjs.sjs.sinajs.cn/t3/platform/js/api/wb.js');
            Yii::app()->clientScript->registerCssFile('http://tjs.sjs.sinajs.cn/t3/style/css/common/card.css');
        ?>

        <script type="text/javascript">
        WB.core.load(['connect', 'client', 'widget.base', 'widget.atWhere'], function() {
        var cfg = {
            key: 'YOU APP KEY',
            xdpath:'http://jssdk.sinaapp.com/_html/xd.html',
        };
        WB.connect.init(cfg);
        WB.client.init(cfg);

        //follow you on sina weibo
        WB.widget.base.followButton('12345', document.getElementById("followBtn"));
        });
        //  </script>
        </div>
   */ ?>
    </div>

</div>
<!-- Place this tag in your head or just before your close body tag. -->
<script>
$(".hint").tooltip({'placement':'right'});
$(".image-hint").tooltip({'placement':'top'});

$(".content-popup").popover({'placement':'right'});

$(document).ready(function(){
/*        var sortColumn = 0;
        switch (getCookie("file_sort_column")) {
            case 'dataset.identifier':
                sortColumn = 0;
                break;
            case 'name':
                sortColumn = 1;
                break;
            case 'type_id':
                sortColumn = 3;
                break;
            case 'format_id':
                sortColumn = 4;
                break;
            case 'size':
                sortColumn = 5;
                break;
            case 'date_stamp':
                sortColumn = 6;
                break;
            default:
                sortColumn = 0;
                break;
        }
        $("#file-table").tablesorter({
            sortList: [[sortColumn,getCookie("file_sort_order")]]
        });*/
        $('#sample-table').tablesorter();
    });
</script>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
