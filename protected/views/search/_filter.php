<script>
$(function(){
    $(".filter-content").hide();
    //toggle the componenet with class msg_body
    $(".heading").click(function()
    {
      $(this).next(".filter-content").slideToggle(500);
    });
    /*$('#filter_form input:checkbox').click(function(){
        document.forms['filter_form'].submit();
    });*/
});
</script>
<?
$datasettypes=array();
$projects=array();
$file_types=array();
$file_formats=array();
$common_names=array();
$external_link_types=array();


for($i = 0 ; $i < count($dataset_data) ; $i++){
    $temp_dataset=$dataset_data[$i];

    #Dataset Types
    $temp_dataset_types=$temp_dataset->datasetTypes;
    foreach ($temp_dataset_types as $key => $temp_dataset_type) {
        $datasettypes[$temp_dataset_type->id]= $temp_dataset_type->name;
    }
    asort($datasettypes);

    #Projects
    $temp_projects = $temp_dataset->projects;
    foreach ($temp_projects as $key => $project) {
         $projects[$project->id]=$project->name;
    }
    asort($projects);

    # Common Names
    $samples=$temp_dataset->samples;
    foreach ($samples as $key => $sample) {
        if(is_numeric($sample->species_id)){
            $common_names[$sample->species_id] = $list_common_names[$sample->species_id];
        }
    }
    asort($common_names);

    #External Link Type
    $temp_external_links = $temp_dataset->externalLinks;
    foreach ($temp_external_links as $key => $external_link) {
        if(is_numeric($external_link->external_link_type_id)){

            $typeNameLabel = preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$list_external_link_types[$external_link->external_link_type_id]);
            $typeNameLabel = preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $typeNameLabel);
            $typeNameLabel = trim($typeNameLabel);

            $external_link_types[$external_link->external_link_type_id] = $typeNameLabel ;
        }
    }
    asort($external_link_types);

}

for($i = 0 ; $i < count($file_data) ; $i++){

    $file = $file_data[$i];
    #File Types & File Format
    if(is_numeric($file->type_id)) {
        $file_types[$file->type_id]=$list_file_types[$file->type_id];
    }
    if(is_numeric($file->format_id)){
        $file_formats[$file->format_id]=$list_file_formats[$file->format_id];
    }
}

?>
    <a data-toggle="modal" href="#how-to-use-filters" class="btn filter"><?=Yii::t('app' , 'How to use filters')?></a>
<div class="modal hide fade" id="how-to-use-filters">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>How to use filters</h3>
  </div>
  <div class="modal-body">
    <p>To see all search results for your keywords, leave all these filters disabled or disable a filter if it is already enabled. If you want to hide some results based on some criteria, choose the filter for your criteria, and select the options that match what you want to see. Search results that are not defined for a filter will show only if the filter is disabled (default).</p>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">Close</a>
  </div>
</div>
<?php
    echo MyHtml::beginForm('/search/index','GET',array('id'=>'filter_form'));
    echo MyHtml::hiddenField("keyword",$model->keyword);
    echo MyHtml::hiddenField("tab",$model->tab,array('id'=>'filter_tab'));
    echo MyHtml::hiddenField("exclude",$model->exclude);
    echo MyHtml::submitButton(Yii::t('app' ,'Apply Filters'), array('class'=>'span2 btn-green filter'));


?>

<!-- FILTERS FOR DATASETS -->
    <div id="dataset_filter">
        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'Common Name')?></h4>
            <div class='filter-content'>
                <button class="btn btn_filter" id="btn_common_name"><? if(empty($model->common_name)) echo Yii::t('app' , 'Enable All'); else echo Yii::t('app' , 'Disable'); ?></button>
                <div class="options <? if(empty($model->common_name)) echo 'disabled'; ?> ">
                    <? echo MyHtml::checkBoxList("common_name",$model->common_name, $common_names,array('class'=>'common_name')); ?>
                </div>
            </div>
        </div>
        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'Dataset Type')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_dataset_type"><? if(empty($model->dataset_type)) echo Yii::t('app' , 'Enable All'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->dataset_type)) echo 'disabled'; ?>">
                <? echo MyHtml::checkBoxList("dataset_type",$model->dataset_type, $datasettypes,array('class'=>'dataset_type')); ?>
            </div>
            </div>
        </div>

        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'Project')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_project"><? if(empty($model->project)) echo Yii::t('app' , 'Enable All'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->project)) echo 'disabled'; ?>">
                <?  echo MyHtml::checkBoxList("project",$model->project, $projects,array('class'=>'project'));?>
            </div>
            </div>
        </div>
        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'External Link Types')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_link"><? if(empty($model->external_link_type)) echo Yii::t('app' , 'Enable All'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->external_link_type)) echo 'disabled'; ?>">
                <?  echo MyHtml::checkBoxList("external_link_type",$model->external_link_type, $external_link_types,array('class'=>'external_link_type'));?>
            </div>
            </div>
        </div>

        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'Publication Date')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_publication_date"><? if(empty($model->pubdate_from) && empty($model->pubdate_to)) echo Yii::t('app' , 'Enable'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->pubdate_from) && empty($model->pubdate_to)) echo 'disabled'; ?>">
                <label>From</label><? echo Myhtml::textField("pubdate_from",$model->pubdate_from,array('class'=>'date','placeholder'=>'dd-mm-yyyy')); ?>
                <div style="clear:both"></div>
                <label>To</label> <? echo Myhtml::textField("pubdate_to",$model->pubdate_to,array('class'=>'date','placeholder'=>'dd-mm-yyyy')); ?>
                <div style="clear:both"></div>
            </div>
            </div>
        </div>

        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'Modification Date')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_modification_date"><? if(empty($model->moddate_from) && empty($model->moddate_to)) echo Yii::t('app' , 'Enable'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->moddate_from) && empty($model->moddate_to)) echo 'disabled'; ?>">
                <label>From</label> <? echo Myhtml::textField("moddate_from",$model->moddate_from,array('class'=>'date','placeholder'=>'dd-mm-yyyy')); ?>
                <div style="clear:both"></div>
                <label>To</label> <? echo Myhtml::textField("moddate_to",$model->moddate_to,array('class'=>'date','placeholder'=>'dd-mm-yyyy')); ?>
                <div style="clear:both"></div>
            </div>
            </div>
        </div>


    </div>

<!-- FILTERS FOR FILES -->

    <div id="file_filter" style="display:none;">
        <div class="filter" >
        <h4 class='heading'><?=Yii::t('app' , 'File Type')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_file_type"><? if(empty($model->file_type)) echo Yii::t('app' , 'Enable All'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->file_type)) echo 'disabled'; ?>">
                <?  echo MyHtml::checkBoxList("file_type",$model->file_type, $file_types,array('class'=>'file_type')); ?>
            </div>
            </div>
        </div>
        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'File Format')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_file_format"><? if(empty($model->file_format)) echo Yii::t('app' , 'Enable All'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->file_format)) echo 'disabled'; ?>">
                <? echo MyHtml::checkBoxList("file_format",$model->file_format, $file_formats,array('class'=>'file_format'));?>
            </div>
            </div>
        </div>
        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'File Size')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_release_date"><? if(empty($model->size_from) && empty($model->size_to)) echo Yii::t('app' , 'Enable'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->size_from) && empty($model->size_to)) echo 'disabled'; ?>">
                <label>From</label> <? echo Myhtml::textField("size_from",$model->size_from,array('class'=>'size')); echo Myhtml::dropDownList("size_from_unit",$model->size_from_unit,array("1"=>"KB","2"=>"MB","3"=>"GB","4"=>"TB"),array('class'=>'unit')); ?>
                <div style="clear:both"></div>
                <label>To</label> <? echo Myhtml::textField("size_to",$model->size_to,array('class'=>'size')); echo Myhtml::dropDownList("size_to_unit",$model->size_to_unit,array("1"=>"KB","2"=>"MB","3"=>"GB","4"=>"TB"),array('class'=>'unit'));?>
                <div style="clear:both"></div>
            </div>
            </div>
        </div>
        <div class="filter">
        <h4 class='heading'><?=Yii::t('app' , 'Release Date')?></h4>
            <div class='filter-content'>
            <button class="btn btn_filter" id="btn_release_date"><? if(empty($model->reldate_from) && empty($model->reldate_to)) echo Yii::t('app' , 'Enable'); else echo Yii::t('app' , 'Disable'); ?></button>
            <div class="options <? if(empty($model->reldate_from) && empty($model->reldate_to)) echo 'disabled'; ?>">
                <label>From</label> <? echo Myhtml::textField("reldate_from",$model->reldate_from,array('class'=>'date','placeholder'=>'dd-mm-yyyy')); ?>
                <div style="clear:both"></div>
                <label>To</label> <? echo Myhtml::textField("reldate_to",$model->reldate_to,array('class'=>'date','placeholder'=>'dd-mm-yyyy')); ?>
                <div style="clear:both"></div>
            </div>
            </div>
        </div>
    </div>



    <?php
        echo MyHtml::submitButton(Yii::t('app' ,'Apply Filters'), array('class'=>'span2 btn-green filter'));
        echo MyHtml::endForm();
    ?>
<script>
submitFilter = function(){
    var action=$(this).attr("action");
    var tab=$("#filter_tab").val();
    if(tab!="" && action.indexOf("#") ==-1){
        action=action+tab;
    }
    $(this).attr("action",action);
    $(this).submit();
    return false;
};

$(function () {
    $('.date, .size').focus(function() {
        $(this).parent().removeClass('disabled');
    });
    $('.date, .size').focusout(function() {
        $(this).parent().children("input").each(function(index,ele){
            if($(ele).val()!=""){
                $(this).parent().removeClass('disabled');
                return false;
            }
            $(this).parent().addClass('disabled');
        });
    });
    $('.date, .size').change(function() {

        $(this).parent().children("input").each(function(index,ele){
            if($(ele).val()!=""){
                $(this).parent().removeClass('disabled');
                $(this).parent().parent().find("button").html('<?=Yii::t('app' , 'Disable')?>');
                return false;
            }
            $(this).parent().parent().find("button").html("Enable");
            $(this).parent().addClass('disabled');
        });

    });

    $('.date').datepicker({dateFormat: 'dd-mm-yy'});

    $('.btn_filter').click(function () {
        var action=$(this).html();
        var alt="";
        if($(this).next().has("input:text").length==0){
            alt="Enable All";
        }else{
            alt="Enable";
        }

        var status;
        if(action=='<?=Yii::t('app' , 'Disable')?>'){
            $(this).html(alt);
            status=false;
            $(this).next().addClass('disabled');
        }else {
            $(this).html('<?=Yii::t('app' , 'Disable')?>');
            status=true;
            $(this).next().removeClass('disabled');
        }
        $(this).next().find(':checkbox').attr('checked', status);
        $(this).next().find(':text').attr('value', "");
        document.forms['filter_form'].submit(submitFilter);
        return false;
    });

    $('input:checkbox').click(function (e) {
        var disable=true;
        $(this).parent().children("input:checkbox").each(function(index,ele){
            if($(ele).attr("checked")){
                $(ele).parent().parent().parent().children("button").html('<?=Yii::t('app' , 'Disable')?>');
                $(ele).parent().parent().removeClass('disabled');
                disable = false;
            }
        });
        if(disable){
            $(this).parent().parent().parent().children("button").html("Enable All");
            $(this).parent().parent().addClass('disabled');
        }
        document.forms['filter_form'].submit(submitFilter);
    });

});


$('#filter_form').submit(submitFilter);
//$('#filter_form').submit(function() {
//    var action=$(this).attr("action");
//    var tab=$("#filter_tab").val();
//    if(tab!="" && action.indexOf("#") ==-1){
//        action=action+tab;
//    }
//    $(this).attr("action",action);
//    $(this).submit();
//    return false;
//});

$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

</script>
