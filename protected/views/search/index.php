<?
    // There are search results
    $this->renderPartial('_search',array('model'=>$model));
?>
    <div class="row" id="form_result">
    	<div class="span3" id="filter">
	    	<? $this->renderPartial("_filter",array('model'=>$model,
                                #'dataset_data'=>$full_dataset_result,
                                #'file_data'=>$full_file_result,
                                'dataset_data'=>$search_result['dataset_result']->getData(),
                                'file_data'=>$search_result['file_result']->getData(),
                                'datasetTypes'=>$datasetTypes,
                                'list_file_types'=>$file_types,
                                'list_file_formats'=>$file_formats,
                                'list_common_names'=>$list_common_names,
                                'list_external_link_types'=>$list_external_link_types)); ?>
		</div>
		<div class="span9 result" id="result">
                    <?if($total_files_found > $search_result['file_result']->totalItemCount){
                        $files_count = "$total_files_found (displaying first " . $search_result['file_result']->totalItemCount . ")";
                    } else {
                        $files_count = $total_files_found ;
                    }?>
                        <span class='pull-right'><?=Yii::t('app' , 'Dataset Results:')?> <?= $search_result['dataset_result']->totalItemCount?>, <?=Yii::t('app' , 'File Results:')?> <?=$files_count?></span>
	    	<? $this->renderPartial("_result",array('model'=>$model,'search_result'=>$search_result,'list_file_types'=>$file_types,'list_file_formats'=>$file_formats,'filesort_column'=>$filesort_column,'filesort_order'=>$filesort_order , 'exclude'=>$exclude )); ?>
		</div>


    </div>
<script>
    $(".hint").tooltip({'placement':'left'});
    $(".content-popup").popover({'placement':'right'});
    $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
        var rel=$(this).attr("rel");
        $(rel).show();
        if(rel=="#file_filter"){
            //window.location.hash="#result_files";
            $("#filter_tab").val("#result_files");
            $("#dataset_filter").hide();
        }else if(rel=="#dataset_filter"){
            $("#file_filter").hide();
            //window.location.hash="#result_dataset";
            $("#filter_tab").val("#result_dataset");
        }

    });

    if(window.location.hash=="#result_files"){
        $("#myTab a[href='#result_files']").tab("show");
        $("#file_filter").show();
        $("#dataset_filter").hide();

    }
</script>







