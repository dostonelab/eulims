<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\lab\Customer;
use common\models\finance\Collectiontype;
use yii\helpers\ArrayHelper;
use kartik\widgets\DatePicker;
use kartik\daterange\DateRangePicker;
use yii\db\Query;
use common\models\finance\PaymentStatus;
/* @var $this yii\web\View */
/* @var $searchModel common\models\finance\Op */
/* @var $dataProvider yii\data\ActiveDataProvider */

use common\components\Functions;

$func= new Functions();
$this->title = 'Order of Payment';
$this->params['breadcrumbs'][] = ['label' => 'Finance', 'url' => ['/finance']];
$this->params['breadcrumbs'][] = 'Order of Payment';
$this->registerJsFile("/js/finance/finance.js");
$CustomerList= ArrayHelper::map(Customer::find()->all(),'customer_id','customer_name' );

$Header="Department of Science and Technology<br>";
$Header.="Order of Payment";
?>
<div class="orderofpayment-index">
    <?php
        echo $func->GenerateStatusLegend("Legend/Status",true);
    ?>
    <p>
       
    </p>
    
    
    
  <div class="table-responsive">
  
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax'=>true,
        'pjaxSettings' => [
            'options' => [
                'enablePushState' => false,
            ]
        ],
        'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'before'=>Html::button('<span class="glyphicon glyphicon-plus"></span> Create Order of Payment', ['value'=>'/finance/op/create', 'class' => 'btn btn-success','title' => Yii::t('app', "Create New Order of Payment"),'id'=>'btnOP']),
                'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
            ],
        'exportConfig'=>$func->exportConfig("Order of Payment", "op", $Header),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

           // 'orderofpayment_id',
           // 'rstl_id',
            'transactionnum',
            [
                'attribute' => 'collectiontype_id',
                'label' => 'Collection Type',
                'value' => function($model) {
                    return $model->collectiontype->natureofcollection;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => ArrayHelper::map(Collectiontype::find()->asArray()->all(), 'collectiontype_id', 'natureofcollection'),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Collection Type', 'id' => 'grid-op-search-collectiontype_id']
            ],
            [
               'attribute'=>'order_date',
               'filterType'=> GridView::FILTER_DATE_RANGE,
               'value' => function($model) {
                    return date_format(date_create($model->order_date),"m/d/Y");
                },
                'filterWidgetOptions' => ([
                     'model'=>$model,
                     'useWithAddon'=>true,
                     'attribute'=>'order_date',
                     'startAttribute'=>'createDateStart',
                     'endAttribute'=>'createDateEnd',
                     'presetDropdown'=>TRUE,
                     'convertFormat'=>TRUE,
                     'pluginOptions'=>[
                         'allowClear' => true,
                        'locale'=>[
                            'format'=>'Y-m-d',
                            'separator'=>' to ',
                        ],
                         'opens'=>'left',
                      ],
                     'pluginEvents'=>[
                        "cancel.daterangepicker" => "function(ev, picker) {
                        picker.element[0].children[1].textContent = '';
                        $(picker.element[0].nextElementSibling).val('').trigger('change');
                        }",
                        
                        'apply.daterangepicker' => 'function(ev, picker) { 
                        var val = picker.startDate.format(picker.locale.format) + picker.locale.separator +
                        picker.endDate.format(picker.locale.format);

                        picker.element[0].children[1].textContent = val;
                        $(picker.element[0].nextElementSibling).val(val);
                        }',
                      ] 
                     
                ]),        
               
            ],
          
            [
                'attribute' => 'customer_id',
                'label' => 'Customer Name',
                'value' => function($model) {
                    return $model->customer->customer_name;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => ArrayHelper::map(Customer::find()->asArray()->all(), 'customer_id', 'customer_name'),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Customer Name', 'id' => 'grid-op-search-customer_id']
            ],
           
            [
               'label'=>'Status', 
               'format'=>'raw',
               'value'=>function($model){
                    $Obj=$model->getCollectionStatus($model->orderofpayment_id);
                    if($Obj){
                       return "<button class='btn ".$Obj[0]['class']." btn-block'><span class=".$Obj[0]['icon']."></span>".$Obj[0]['payment_status']."</button>"; 
                    }else{
                       return "<button class='btn btn-primary btn-block'>Unpaid</button>"; 
                    }
                   //
                },   
            ],
            [
              //'class' => 'yii\grid\ActionColumn'
                'class' => kartik\grid\ActionColumn::className(),
                'template' => "{view}{update}{delete}",
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => '/finance/op/view?id=' . $model->orderofpayment_id,'onclick'=>'location.href=this.value', 'class' => 'btn btn-primary', 'title' => Yii::t('app', "View Order of Payment")]);
                    },
                    'update' => function ($url, $model) {
                        $Obj=$model->getCollectionStatus($model->orderofpayment_id);
                        return $Obj[0]['payment_status_id'] ? ($Obj[0]['payment_status_id'] == 1 ? Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => '/finance/op/update?id=' . $model->orderofpayment_id, 'onclick' => 'LoadModal(this.title, this.value);', 'class' => 'btn btn-success', 'title' => Yii::t('app', "Update Order of Payment]")]) : '') : "";
                        //return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => '/finance/op/update?id=' . $model->orderofpayment_id, 'onclick' => 'LoadModal(this.title, this.value);', 'class' => 'btn btn-success', 'title' => Yii::t('app', "Update Order of Payment]")]);
                    },
                    'delete' => function ($url, $model) {
                        $Obj=$model->getCollectionStatus($model->orderofpayment_id);
                        return Html::button('<span class="glyphicon glyphicon-ban-circle"></span>', ['value' => '/finance/cancelop/create?op=' . $model->orderofpayment_id,'onclick' => 'LoadModal(this.title, this.value,true,"420px");', 'class' => 'btn btn-danger','disabled'=>$Obj[0]['payment_status'] <> 'Unpaid', 'title' => Yii::t('app', "Cancel Order of Payment")]);
                    }        
                    /*'delete' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-ban-circle"></span>', ['value' => '/lab/cancelrequest/create?req=' . $model->request_id,'onclick' => 'LoadModal(this.title, this.value,true,"420px");', 'class' => 'btn btn-danger','disabled'=>$model->status_id==2, 'title' => Yii::t('app', "Cancel Request")]);
                    }*/
                ],
            ],

        ],
    ]); ?>
      
     <?php
    // This section will allow to popup a notification
    $session = Yii::$app->session;
    if ($session->isActive) {
        $session->open();
        if (isset($session['deletepopup'])) {
            $func->CrudAlert("Deleted Successfully","WARNING");
            unset($session['deletepopup']);
            $session->close();
        }
        if (isset($session['updatepopup'])) {
            $func->CrudAlert("Updated Successfully");
            unset($session['updatepopup']);
            $session->close();
        }
        if (isset($session['savepopup'])) {
            $func->CrudAlert("Successfully Saved","SUCCESS",true);
            unset($session['savepopup']);
            $session->close();
        }
        if (isset($session['errorpopup'])) {
            $func->CrudAlert("Transaction Error","ERROR",true);
            unset($session['errorpopup']);
            $session->close();
        }
        if (isset($session['checkpopup'])) {
            $func->CrudAlert("Insufficient Wallet Balance","INFO",true,false,false);
            unset($session['checkpopup']);
            $session->close();
        }
    }
    ?>
  </div>
</div>
<script type="text/javascript">
    $('#btnOP').click(function(){
        $('.modal-title').html($(this).attr('title'));
        $('#modal').modal('show')
            .find('#modalContent')
            .load($(this).attr('value'));
    });
  
</script>
