<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\grid\BooleanColumn;

/* @var $this yii\web\View */
/* @var $searchModel common\models\finance\BillingReceiptSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Statement of Accounts';
$this->params['breadcrumbs'][] = ['label' => 'Finance', 'url' => ['/finance']];
$this->params['breadcrumbs'][] = ['label' => 'Billing', 'url' => ['/finance/billing']];
$this->params['breadcrumbs'][] = $this->title;
$pdfHeader="Enhanced ULIMS";
$pdfFooter="{PAGENO}";
?>
<div class="billing-receipt-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Create Statement of Account', ['value'=>'/finance/soa/create','onclick'=>'ShowModal(this.title,this.value)', 'class' => 'btn btn-success','title' => Yii::t('app', "Create Statement of Account"),'id'=>'btnSOA']); ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'responsive'=>false,
        'hover' => true,
        'showPageSummary' => true,
        'pjax'=>true,
        'pjaxSettings' => [
            'options' => [
                'enablePushState' => false,
            ]
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => "",
        ],
        'rowOptions'=>function($model){
            if($model->payment_due_date<date("Y-m-d")){
                return ['style' => 'color: red;font-weight: bold'];
            }
        },
        'exportConfig'=>[
            GridView::PDF => [
                'filename' => 'statement_of_account',
                'alertMsg'        => 'The PDF export file will be generated for download.',
                'config' => [
                    'methods' => [
                        'SetHeader' => [$pdfHeader],
                        'SetFooter' => [$pdfFooter]
                    ],
                    'options' => [
                        'title' => 'Statement of Account List',
                        'subject' => 'SOA',
                        'keywords' => 'pdf, preceptors, export, other, keywords, here'
                    ],
                ]
            ],
            GridView::EXCEL => [
                'label'           => 'Excel',
                //'icon'            => 'file-excel-o',
                'methods' => [
                    'SetHeader' => [$pdfHeader],
                    'SetFooter' => [$pdfFooter]
                ],
                'iconOptions'     => ['class' => 'text-success'],
                'showHeader'      => TRUE,
                'showPageSummary' => TRUE,
                'showFooter'      => TRUE,
                'showCaption'     => TRUE,
                'filename'        => "statement of account",
                'alertMsg'        => 'The EXCEL export file will be generated for download.',
                'options'         => ['title' => 'Department of Science OneLab'],
                'mime'            => 'application/vnd.ms-excel',
                'config'          => [
                    'worksheet' => 'Statement of Account',
                    'cssFile'   => ''
                ]
            ],
        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute'=>'soa_date',
                'label'=>'Date',
                'hAlign' => 'center',
                'value'=>function($model){
                    return date("m/d/Y",strtotime($model->soa_date));
                }
            ],
            [
                'attribute'=>'payment_due_date',
                'label'=>'Due Date',
                'hAlign' => 'center',
                'value'=>function($model){
                    return date("m/d/Y",strtotime($model->payment_due_date));
                }
            ],
            [
                'attribute'=>'soa_number',
                'hAlign' => 'center',
            ],
            [
                'attribute'=>'customer_id',
                'label'=>'Customer',
                'value'=>function($model){
                    return $model->customer->customer_name;
                },
                'hAlign' => 'left',
                'pageSummary'=>'TOTAL'
            ],
            [
                'attribute'=>'previous_balance',
                'hAlign'=>'right',
                'format' => ['decimal', 2],
                'pageSummary' => true,
            ],
            [
                'attribute'=>'current_amount',
                'hAlign'=>'right',
                'format' => ['decimal', 2],
                'pageSummary' => true,
            ], //payment_amount
            [
                'attribute'=>'payment_amount',
                'hAlign'=>'right',
                'format' => ['decimal', 2],
                'pageSummary' => true,
            ],
            [
                'attribute'=>'total_amount',
                'label'=>'Total',
                'hAlign'=>'right',
                'format' => ['decimal', 2],
                'pageSummary' => true,
            ],
            [
                'class' => kartik\grid\ActionColumn::className(),
                'template' => "{view}",
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => '/finance/soa/view?id=' . $model->customer_id,'onclick'=>'ShowModal(this.title,this.value,true,"900px")', 'class' => 'btn btn-primary', 'title' => Yii::t('app', "View Statement of Accounts")]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>