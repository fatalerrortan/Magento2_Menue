<?php
namespace Nextorder\Menue\Controller\Nutrition;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action{
    protected $_nGoalsFactory;
    public function __construct(Context $context, \Nextorder\Menue\Model\NgoalsFactory $nGoalsFactory){
        $this->_nGoalsFactory = $nGoalsFactory;
        parent::__construct($context);
    }
    public function execute(){
        $abnehmen = '{
            "userrule":[
                {
                    "attr":"bmi",
                    "type":"customer",
                    "operator":">",
                    "value":"25",
                    "unit":"",
                    "error_handle":"none"
                }
            ],
            "singlerule":[
                {
                    "attr":"nof_calories",
                    "type":"product",
                    "operator":"<=",
                    "value":"24 * $user[\'body_weight\'] * 0.4",
                    "unit":"kcal",
                    "error_handle":"reload"
                }
            ]
        }';
        $zunehmen = '{
            "userrule": [],
            "singlerule":[
                {
                    "attr":"nof_calories",
                    "type":"product",
                    "operator":">=",
                    "value":"24*$user[\'body_weight\']*(1+0.2/10*($user[\'target_weight\']-$user[\'body_weight\']))*0.4",
                    "unit":"kcal",
                    "error_handle":"complement"
                }
            ]
        }';
        $gesud = '{
            "userrule":[],
            "singlerule":[
                {
                    "attr":"nof_calories",
                    "type":"product",
                    "operator":">=",
                    "value":"24*$user[\'body_weight\']*$user[\'work_intensity\']*0.4",
                    "unit":"kcal",
                    "error_handle":"complement"
                }
            ]
        }';
        $muskelaufbau = '{
            "userrule":[],
            "singlerule":[
                {
                    "attr":"nof_carbs",
                    "type":"product",
                    "operator":"<=",
                    "value":"24*$user[\'body_weight\']*$user[\'work_intensity\']*0.4*0.25*0.25",
                    "unit":"g",
                    "error_handle":"reload"
                },
                {
                    "attr":"nof_protein",
                    "type":"product",
                    "operator":">=",
                    "value":"24*$user[\'body_weight\']*$user[\'work_intensity\']*0.4*0.4*0.25",
                    "unit":"g",
                    "error_handle":"complement"
                },
                {
                    "attr":"nof_fat",
                    "type":"product",
                    "operator":">=",
                    "value":"24*$user[\'body_weight\']*$user[\'work_intensity\']*0.4*0.35*0.11",
                    "unit":"g",
                    "error_handle":"complement"
                }
            ]
        }';
        $nutritionModel = $this->_nGoalsFactory->create();
        $nutritionModel->setLabel('Abnehmen')->setDef($abnehmen)->save();
        echo 'new nutrition defnition was saved';
    }
}