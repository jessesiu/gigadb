<script type="text/javascript" src="/assets/e18ec003/jquery.js"></script>
<script type="text/javascript" src="/assets/e18ec003/jquery.ba-bbq.js"></script>
<script type="text/javascript" src="/assets/36091b3c/js/bootstrap.min.js"></script>

<div class="tab-pane active" id="result_dataset">
    <table class="table table-bordered" id ="list">
        <tr>
            <th colspan="9"><?= Yii::t('app', 'Your Submitted Datasets') ?></th>
        </tr>
        <tr>
            <th class="span2"><?= Yii::t('app', 'DOI') ?></th>
            <th class="span6"><?= Yii::t('app', 'Title') ?></th>
            <th class="span6"><?= Yii::t('app', 'Subject') ?></th>
            <th class="span2"><?= Yii::t('app', 'Dataset Type') ?></th>
            <th class="span2"><?= Yii::t('app', 'Status') ?></th>
            <th class="span2"><?= Yii::t('app', 'Publication Date') ?></th>
            <th class="span2"><?= Yii::t('app', 'Modification Date') ?></th>
            <th class="span2"><?= Yii::t('app','File Count') ?></th>
            <th class="span2"><?= Yii::t('app', 'Operation') ?></th>
        </tr>

        <?php
        for ($i = 0; $i < count($uploadedDatasets); $i++) {
            $class = $i % 2 == 0 ? 'even' : 'odd';
            if(isset($selected) && $i==$selected)
                $class = 'selected';
            $data = $uploadedDatasets;
//            print_r($data[0]);
            ?>
            
            <tr class="<?php echo $class; ?>">
                <?
                $upload_status = $data[$i]->upload_status;
                
                if ( $upload_status != 'Published' && $upload_status!='Private' ) { ?>
                    <td class="content-popup" data-content="<? echo MyHtml::encode($data[$i]->description); ?>">
                       unknown
                        
                    </td>
                <? } else { ?>
                    <td class="content-popup" data-content="<? echo MyHtml::encode($data[$i]->description); ?>">
                        <? echo MyHtml::link("10.5524/" . $data[$i]->identifier, "/dataset/" . $data[$i]->identifier, array('target' => '_blank')); ?>
                    </td>
                <? } ?>
                <td class="left content-popup" data-content="<? echo MyHtml::encode($data[$i]->description); ?>"><? echo $data[$i]->title; ?> </td>
                <td><? echo $data[$i]->commonNames; ?> </td>
                <td >
                    <? foreach ($data[$i]->datasetTypes as $type) { ?>
                        <?= $type->name ?>

                    <? } ?>
                </td>
                <td><?= MyHtml::encode($data[$i]->upload_status) ?></td>
                <td><? echo MyHtml::encode($data[$i]->publication_date); ?> </td>
                <td><? echo MyHtml::encode($data[$i]->modification_date); ?> </td>
                <td><? echo count($data[$i]->files); ?></td>
                <td>
                  <? if ($data[$i]->upload_status !='Published' && $data[$i]->upload_status!='Pending' && $data[$i]->upload_status!='Private'){ ?>
                    <a class="update" title="Update" href=<? echo "/dataset/updateSubmit/?id=" . $data[$i]->id ?> ><img src="/assets/4a0237a/gridview/update.png" alt="Update" /></a>
                    <a class="delete" title="Delete" href=<? echo "/dataset/delete/id/" . $data[$i]->id; ?>><img src="/assets/4a0237a/gridview/delete.png" alt="Delete" /></a>
                    
                  <? } ?>
                </tr>
            <? } ?>
    </table>
</div>
<script>
    $(".hint").tooltip({'placement': 'left'});

    $('a.delete').live('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?'))
            return false; 
      e.preventDefault();
      $.ajax({
           type: 'POST',
           url: $(this).attr('href'),
           success: function(){
                window.location.reload();
            
          },
          error:function(){
            alert("Failure!")
//          $("#result").html('there is error while submit');
      }   
          
        });
        

    });

</script>
