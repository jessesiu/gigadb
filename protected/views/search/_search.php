<div class="row">
    <div class="span12">
    <?php echo MyHtml::beginForm('/search/index','GET',array('class'=>'form-search well','onsubmit'=>'return validateForm(this);')); ?>
    <?php echo MyHtml::errorSummary($model); ?>
    <?php

        $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
            'name'=>'keyword',
            //'source'=>array('ac1', 'ac2', 'ac3'),
            // 'source'=> array_values($dataset->getListTitles()),
            'source'=> array_values(array()),
            // additional javascript options for the autocomplete plugin
            'options'=>array(
                 'minLength'=>'2',
             ),
            'htmlOptions'=>array(
                 'class'=>'span7 offset1',
             ),
             'value'=>$model->keyword
        ));

        echo MyHtml::submitButton(Yii::t('app' , 'Search again'), array('class'=>'span2 btn-green'));
        if(!Yii::app()->user->isGuest) {
            Yii::app()->clientScript->registerScriptFile('/js/jquery.blockUI.js');
            echo MyHtml::ajaxButton(Yii::t('app' , 'Save current search criteria'),array("/search/save"),array('type'=>'POST','dataType'=>'json','data'=>array('criteria'=>$model->criteria) ,'success'=>"function(data){
                if(data.status=='fail'){
                    $('#saveSearchReason').html(data.reason);
                    displaySaveStatus('#saveSearchFail');
                }else {
                    displaySaveStatus('#saveSearchSuccess');
                }
            }") ,array('class'=>'span3 btn'));
        }
    ?>


    <?php echo MyHtml::endForm(); ?>

    </div>
</div>

<div id="saveSearchStatus" >
    <div id="saveSearchSuccess" style="display:none">
        <h2>Success</h2>
    </div>
    <div id="saveSearchFail" style="display:none">
        <h2>Opps!</h2>
        <h2 id="saveSearchReason">Reason</h2>
    </div>
</div>

<script>

function displaySaveStatus(block){
    $.blockUI({
        message: $(block),
        fadeIn: 700,
        fadeOut: 700,
        timeout: 2000,
        showOverlay: false,
        centerY: false,
        css: {
            width: '350px',
            top: '10px',
            left: '',
            right: '10px',
            border: 'none',
            padding: '5px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .6,
            color: '#fff'
        }
    });
}


function validateForm(myform){
    if(myform.keyword.value.length==0) {
        alert("Keyword can not be blank");
        return false;
    }

    return true;

}


// function submitForm(myform){
//     var strJson= JSON.stringify($(myform).serializeObject());

//     var searchform=myform['searchform'].value;


//     var url=window.location.protocol+"//"+window.location.hostname+(window.location.port ? ':'+window.location.port: '')+$(myform).attr("action");

//     window.location = url+"?criteria=" + strJson;



//     return false;

// }

// $.fn.serializeObject = function()
// {
//     var o = {};
//     var a = this.serializeArray();
//     $.each(a, function() {
//         if (o[this.name] !== undefined) {
//             if (!o[this.name].push) {
//                 o[this.name] = [o[this.name]];
//             }
//             o[this.name].push(this.value || '');
//         } else {
//             o[this.name] = this.value || '';
//         }
//     });
//     return o;
// };

</script>
