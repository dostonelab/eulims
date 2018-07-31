<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use frontend\modules\finance\components\models\Ext_Request;
$js=<<<SCRIPT
   $(".kv-row-checkbox").click(function(){
        settotal();
   });    
   $(".select-on-check-all").change(function(){
        settotal();
   });
  
SCRIPT;
$this->registerJs($js);

?>

 <?php 
    $gridColumn = [
        [
            'class' => '\kartik\grid\SerialColumn',
            
         ],
       
         [
             'class' => '\kartik\grid\CheckboxColumn',
             'checkboxOptions' => function($model) {
                if($model->posted == 1 && $model->payment_status_id <> 2){
                   return ['disabled' => true];
                }else{
                   return [];
                }
             },
         ],
        [
          'attribute'=>'request_ref_num',
          'enableSorting' => false,
        ],
//        [
//            'attribute'=>'request_datetime',
//             'value' => function($model) {
//                    return date($model->request_datetime);
//                },
//            'pageSummary' => '<span style="float:right;">Total</span>',
//            'enableSorting' => false,
//        ],
        [
            'attribute'=>'payment_status_id',
            'format'=>'raw',
             'value' => function($model) {
                    //return $model->PaymentStatusDetailspayment_status_id;
                    $Obj=$model->getPaymentStatusDetails($model->request_id);
                    if($Obj){
                       return "<span class='badge ".$Obj[0]['class']." legend-font' style='width:80px!important;height:20px!important;'>".$Obj[0]['payment_status']."</span>";
                    }else{
                        return "<span class='badge btn-primary legend-font' style='width:80px!important;height:20px!important;'>Unpaid</span>";
                    }
            },
            'enableSorting' => false,
             'hAlign'=>'center',      
        ],               
        [
            'attribute'=>'total',
            'enableSorting' => false,
            'contentOptions' => [
                'style'=>'max-width:80px; overflow: auto; white-space: normal; word-wrap: break-word;'
            ],
            'value' => function($model) {
                 $request= Ext_Request::find()->where(['request_id' => $model->request_id])->one();
                 $total=$request['total'];
                 return $model->getBalance($model->request_id,$total);
            },
            'hAlign' => 'right', 
            'vAlign' => 'middle',
            'width' => '7%',
            'format' => ['decimal', 2],
            'pageSummary' => '<span id="total">0.00</span>',
             
        ],
                 
         /*[
            'attribute'=>'selected_request',
            'pageSummary' => '<span style="float:right;">Total</span>',
        ],*/
      
        
    ];
?>    

	   
	<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id'=>'grid',
        'pjax'=>true,
        'containerOptions'=> ["style"  => 'overflow:auto;height:300px'],
        'pjaxSettings' => [
            'options' => [
                'enablePushState' => false,
            ]
        ],
        
        'responsive'=>false,
        'striped'=>true,
        'hover'=>true,
      
        'floatHeaderOptions' => ['scrollingTop' => true],
        'panel' => [
            'heading'=>'<h3 class="panel-title">Request</h3>',
            'type'=>'primary',

         ],
       
         'columns' =>$gridColumn,
         'showPageSummary' => true,
         'toolbar'=> [],
         /*'afterFooter'=>[
             'columns'=>[
                 'content'=>'Total Selected'
             ],
             
         ],*/
    ]); ?>

 <?php 
 if ($stat){
      ?><div class="form-group pull-right">
    <button  id='btnSaveCollection' class='btn btn-success' ><i class='fa fa-save'></i> Add Payment Item</button>
    <?php if(Yii::$app->request->isAjax){ ?>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    <?php } ?>
  </div>
<?php

 }
 else{
     $opid="";
 }
?>


<script type="text/javascript">
    function settotal(){
        
        //
        var dkeys=$("#grid").yiiGridView("getSelectedRows");
        $("#ext_billing-opids").val(dkeys);
        var SearchFieldsTable = $(".kv-grid-table>tbody");
        var trows = SearchFieldsTable[0].rows;
        var Total=0.00;
        var amt=0.00;
        $.each(trows, function (index, row) {
            var data_key=$(row).attr("data-key");
            for (i = 0; i < dkeys.length; i++) { 
                if(data_key==dkeys[i]){
                    amt=StringToFloat(trows[index].cells[4].innerHTML);
                    Total=Total+parseFloat(amt);
                }
            }
        }); 
        //$("#ext_billing-amount-disp").val(Total);
        //
      //  var keys = $('#grid').yiiGridView('getSelectedRows');
        var keylist= dkeys.join();
        
        $("#op-requestids").val(keylist);
       // $("#ext_op-requestid_update").val(keys.join());
        
        var tot=parseFloat(Total);
        var total=CurrencyFormat(tot,2);
        $('#total').html(total);
         var payment_mode=$('#op-payment_mode_id').val()
        if(payment_mode==4){

            wallet=parseInt($('#wallet').val());
            totalVal = parseFloat($('#total').html().replace(/[^0-9-.]/g, ''));
            if( totalVal > wallet) {
              alert("Insufficient customer wallet");
              $('#op-purpose').prop('disabled', true);
              $('#createOP').prop('disabled', true);

            }
            else{
                if(total !== 0){
                    $('#op-purpose').prop('disabled', false);
                    $('#createOP').prop('disabled', false);
                }

            }
        }
         
    }
    
    $('#btnSaveCollection').on('click',function(e) {
        var keys = $('#grid').yiiGridView('getSelectedRows');
        var keylist= keys.join();
        if (keylist == ""){
            alert("Please Select Payment Item");
        }
        else{
             $.post({
            url: 'save-paymentitem?request_ids='+keylist+'&opid=<?php echo $opid ?>', // your controller action
            dataType: 'json',
            success: function(data) {
               location.reload();
            }
            });
        }
       
        
     });
</script>