<?php


namespace Nextorder\Menue\Controller\Validate;
use Magento\Framework\App\Action\Context;

/**
 * Class Index
 * @package Nextorder\Menue\Controller\Validate
 */
class Index extends \Magento\Framework\App\Action\Action{
    protected $_logger;
    protected $_customerSession;
    protected $_helper;
    protected $_productFactory;
    protected $_currentNutritionGoal;
    protected $_preConstants = array(
        'weight_coeff' => 24,
        'energy_lunch_ratio' => 0.4,
        'safe_bmi_limit' => 25, // original 25
        'keto_nutritional_ratio' =>
            ['nof_carbs' => 0.25, 'nof_protein' => 0.4, 'nof_fat' => 0.35],
        'calories_grams_rate' =>
            ['nof_carbs' => 0.25, 'nof_protein' => 0.25, 'nof_fat' => 0.11],
        'work_intensity' =>
            ['sit' => 1.4, 'stand' => 1.6, 'walk' => 1.8, 'hard' => 2]
    );
    public $_error = array();
    protected $_isUserruleErrorExists = false;
    protected $_isSingleruleErrorExists = false;

    public function __construct(Context $context,
                                \Magento\Customer\Model\Session $customerSession,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Psr\Log\LoggerInterface $logger,
                                \Nextorder\Menue\Helper\Data $helper){
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute(){
        // construct Argument 1: menu orders
        $orders = $this->getModifiedOrders($this->getRequest()->getParam('orders'));
        // construct Argument 2: user info
        $user = $this->getCustomerInfo($this->getRequest()->getParam('weight'));
        // construct argument 3: nutrition goal definition
        $goal = $this->getNutritionGoal($user);
        $result = $this->nutritionAlgorithm($orders, $user, $goal);
        echo $result;
    }
    /**
     * nutrition algorithm
     * @param $orders
     * @param $user
     * @param $goal
     * @return string
     */
    protected function nutritionAlgorithm($orders, $user, $goal){
        /**
         * user rule map worker
         * @param $rule
         * @return mixed
         */
        $userruleWorker = function ($rule) use ($user){
                /**
            $attrType = $rule['type'];
           if($attrType === 'customer'){
*/
                $leftValue = $user[$rule['attr']];
                $rightValue = round($rule['value'], 2);
                $operator = $rule['operator'];
                $result = $this->getCompareResult($operator, $leftValue, $rightValue);
                if(!$result){
                    $this->_isUserruleErrorExists = true;
                    $error = [
                        'attr' => $rule['attr'],
                        'label' => $this->_helper->getCustomerAttrLabel($rule['attr'], false),
                        'error_value' => $leftValue,
                        'unit' => $rule['unit'],
                        'operator' => $operator,
                        'required' => $rightValue,
                        'handler' => $rule['error_handle'],
                    ];
                    return $error;
                }
                /**
           }else{

                todo: if the rule here is not related to a customer attribute
               limited amount of a specific food (optional)

            }
         */
        };
        /**
         * single rule map worker
         * @param $productsPerday
         * @return array
         */
        $product = $this->_productFactory->create();
        $singleruleWorker = function ($productsPerday) use ($goal, $product){
            // load ordered 3 products each day
            $products = array_map(function($sku) use ($product){
                return  $product->loadByAttribute('sku', $sku);
            },$productsPerday);
            $rulesPerDayMap = array_map(function ($rule) use ($products){
                $leftValue = $this->getSumLeftValue($rule['attr'], $products);
                $rightValue = round($rule['value'], 2);
                $operator = $rule['operator'];
                $result = $this->getCompareResult($operator, $leftValue , $rightValue);
                if(!$result){
                    $this->_isSingleruleErrorExists = true;
                    $error = [
                        'attr' => $rule['attr'],
                        'label' => $this->_helper->getProductAttrLabel($rule['attr']),
                        'error_value' => $leftValue,
                        'unit' => $rule['unit'],
                        'operator' => $operator,
                        'required' => $rightValue,
                        'handler' => $rule['error_handle'],
                    ];
                    return $error;
                }
            }, $goal['singlerule']);
            return array_filter($rulesPerDayMap);
        };
        /**
         * main logic
         */
        if(!empty($goal['userrule'])){
            $userruleMap = array_map($userruleWorker, $goal['userrule']);
            if($this->_isUserruleErrorExists){
                $error = [
                    'goal' => $user['nof_goal'],
                    'messages' => $userruleMap
                ];
                return $this->formatErrorReport($error);
            }
        }
        $singleruleMap = array_map($singleruleWorker, $orders);
        if($this->_isSingleruleErrorExists){
            $error = [
                'goal' => $user['nof_goal'],
                'messages' => $singleruleMap
            ];
            return $this->formatErrorReport($error, false);
        }else{
            $response = [
                'result' => 'correct'
            ];
            return json_encode($response);
        }
    }
    /**
     * get modified orders structure for the first argument of the nutrition algorithm
     * @param array $orders
     * @return array
     */
    protected function getModifiedOrders($orders = []){
        $orders = array_chunk(explode(',', $orders), 5);
        $filter = function ($item){
            return $item != 'disable';
        };
        $modiOrders = [
            'mon' => array_filter(array_column($orders, 0), $filter),
            'tue' => array_filter(array_column($orders, 1), $filter),
            'wed' => array_filter(array_column($orders, 2), $filter),
            'thu' => array_filter(array_column($orders, 3), $filter),
            'fri' => array_filter(array_column($orders, 4), $filter)
        ];
        return $modiOrders;
    }

    protected function getSumLeftValue($attr, $products){
        $sum = null;
        foreach ($products as $product){
            $sum = $sum + floatval(str_replace(',','.',$product->getData($attr)));
        }
        return $sum;
    }
    /**
     * transpile string operator to math operator and return the calculation result
     * @param $operator
     * @param $left
     * @param $right
     * @return bool
     */
    protected function getCompareResult($operator, $left, $right){
        switch ($operator){
            case '>':
                $result = $left > $right; break;
            case '<':
                $result = $left < $right; break;
            case '=':
                $result = $left === $right; break;
            case '<=':
                $result = $left <= $right; break;
            case '>=':
                $result = $left >= $right; break;
                break;
            default:
                $result = false; break;
        }
        return $result;
    }
    /**
     * get customer data for the second argument of the nutrition algorithm
     * @param $currentWeight
     * @return array
     * @throws \Exception
     */
    protected function getCustomerInfo($currentWeight){
        $customer = $this->_customerSession->getCustomer();
        $newWeight = floatval(str_replace(',','.',$currentWeight));
        $prevWeight = floatval(str_replace(',','.',$customer->getData('body_weight')));
        if($newWeight === $prevWeight){
            $weight = $prevWeight;
        }else{
            $this->_logger->addDebug(print_r($currentWeight, true));
            $weight = $newWeight;
            $customer->setData('body_weight', $currentWeight)->save();
        }
        $user = [
            'nof_goal' => strtolower($this->_helper->getCustomerAttrLabel('nof_goal', true, $customer->getData('nof_goal'))),
            'body_weight' => $weight,
            'target_weight' => floatval(str_replace(',','.',$customer->getData('target_weight'))),
            'body_height' => floatval(str_replace(',','.',$customer->getData('body_height'))),
            'bmi' => $this->getBMI($customer->getData('body_weight'), $customer->getData('body_height')),
            'work_intensity' => $this->getWorkIntensityValue($customer->getData('work_intensity'))
        ];
        return $user;
    }
    /**
     * get target nutrition goal rule for the third argument of the nutrition algorithm
     * @param $user
     * @return mixed
     */
    protected function getNutritionGoal($user){
        $oriGoal = $this->_helper->getNutritionGoalWithString($user['nof_goal']);
        $this->_currentNutritionGoal = $oriGoal;
        $calculateValue = function ($rules) use ($oriGoal, $user){
            foreach ($oriGoal[$rules] as $key => $rule){
                $value = null;
                eval("\$value=".$rule['value'].";");
                $this->_currentNutritionGoal[$rules][$key]['value'] = $value;
            }
        };
        $calculateValue('userrule');
        $calculateValue('singlerule');
        return $this->_currentNutritionGoal;
    }
    /**
     * get work intensity integer value from the top global array
     * @param $optionCode
     * @return mixed
     */
    protected function getWorkIntensityValue($optionCode){
        $key = explode('@',$this->_helper->getCustomerAttrLabel('work_intensity', true, $optionCode))[1];
        return $this->_preConstants['work_intensity'][$key];
    }
    /**
     * Calculate Body mass index using customer weight and height
     * @param $weight
     * @param $height
     * @return float|int
     */
    protected function getBMI($weight, $height){
        return round($weight / pow($height, 2), 2);
    }
    /**
     * generate algorithm json report to the front-end
     * @param array $error
     * @param bool $isUserrule
     * @return string
     */
    protected function formatErrorReport($error = [], $isUserrule = true){
        $response = [
            'result' => 'incorrect',
            'goal' => $error['goal'],
            'type' => $isUserrule? 'userrule' : 'singlerule',
            'report' => array_filter($error['messages'])
        ];
        return json_encode($response);
    }
    /**
     * load ordered Products of each weekday
     * @param $skusToFilter
     * @return $this
     */
//    protected function getProductCollection($skusToFilter){
//        $productCollection = $this->_productCollectionFactory->create()
//            ->addAttributeToSelect('*')->addAttributeToFilter('sku', array('in' => $skusToFilter))->load();
//        return $productCollection;
//    }
}