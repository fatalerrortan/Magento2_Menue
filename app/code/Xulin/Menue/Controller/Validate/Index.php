<?php


namespace Nextorder\Menue\Controller\Validate;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Context;

/**
 * Class Index
 * @package Nextorder\Menue\Controller\Validate
 */
class Index extends \Magento\Framework\App\Action\Action{
    protected $_logger;
    protected $_customerSession;
//    protected $_productAttributeRepository;
//    protected $_customerRepository;
//    protected $_eavAttributeRepository;
    protected $_helper;
    protected $_productFactory;
//    protected $_productCollectionFactory;
    protected $_preConstants = array(
        'weight_coeff' => 24,
        'energy_lunch_ratio' => 0.4,
        'safe_bmi_limit' => 20, // original 25
        'keto_nutritional_ratio' =>
            ['nof_carbs' => 0.25, 'nof_protein' => 0.4, 'nof_fat' => 0.35],
        'calories_grams_rate' =>
            ['nof_carbs' => 0.25, 'nof_protein' => 0.25, 'nof_fat' => 0.11],
        'work_intensity' =>
            ['sit' => 1.4, 'stand' => 1.6, 'walk' => 1.8, 'hard' => 2]
    );
    public $_error = array();
    protected $_isOverallErrorExists = false;

    public function __construct(Context $context,
                                \Magento\Customer\Model\Session $customerSession,
//                                \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
//                                \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
//                                \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Psr\Log\LoggerInterface $logger,
//                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Nextorder\Menue\Helper\Data $helper){
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
//        $this->_customerRepository = $customerRepository;
//        $this->_productAttributeRepository = $productAttributeRepository;
//        $this->_eavAttributeRepository = $eavAttributeRepository;
        $this->_productFactory = $productFactory;
        $this->_helper = $helper;
//        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    public function execute(){
        // construct Argument 1: menu orders
        $orders = $this->getModifiedOrders($this->getRequest()->getParam('orders'));
        // construct Argument 2: user info
        $user = $this->getCustomerInfo();
        // construct argument 3: nutrition goal definition
        $goal = $this->getNutritionGoal($user);
        $result = $this->nutritionAlgorithm($orders, $user, $goal);
        echo $result;
    }


    protected function nutritionAlgorithm($orders, $user, $goal){
        /**
         * overall map worker
         */
        // overall map worker start
        $errorExists = false;
        $overallWorker = function ($rule) use ($orders, $user, $errorExists){
            $attrType = $rule['type'];
            if($attrType === 'customer'){
                $leftValue = $user[$rule['attr']];
                $rightValue = $rule['value'];
                $operator = $rule['operator'];
                $result = $this->getCompareResult($operator, $leftValue, $rightValue);
                if(!$result){
                    $this->_isOverallErrorExists = true;
                    return $rule['error']['message'];
                }
            }else{
                /**
                 * todo: if the rule here is not related to a customer attribute
                 * limited amount of a specific food (optional)
                 */
            }
        };
        /**
         * perDish map worker
         */
        $product = $this->_productFactory->create();
        $perDishWorker = function ($days) use ($user, $goal, $product){
            // load ordered 3 products each day
            $products = array_map(function($sku) use ($product){
                return  $product->loadByAttribute('sku', $sku);
            },$days);
            $rulesPerDayMap = array_map(function ($rule) use ($products){
                $leftValue = $this->getSumLeftValue($rule['attr'], $products);
                $rightValue = $rule['value'];
                $operator = $rule['operator'];
                $this->_logger->addDebug(print_r($leftValue, true));
            }, $goal['perDish']);
//        return $rulesPerDayMap;
        };
        /**
         * main logic
         */
        if(!empty($goal['overall'])){
            $overallMap = array_map($overallWorker, $goal['overall']);
            if($this->_isOverallErrorExists){
                /**
                 * todo: show error on the front-end and break algorithm
                 */
                return $this->generateErrorReport($overallMap);
            }
        }
        $perDishMap = array_map($perDishWorker, $orders);


        $response_tmp = [
            'result' => 'incorrect',
            'report' => 'Ihre Auswähle passen nicht Ihrem Ernährungsziel! Bitte täglich Einmal Salat'
        ];
        return json_encode($response_tmp);
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
            $sum = $sum + $product->getData($attr);
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
     * @return array
     */
    protected function getCustomerInfo(){
        $customer = $this->_customerSession->getCustomer();
        $user = [
            'nof_goal' => strtolower($this->_helper->getCustomerAttrLabel('nof_goal', $customer->getData('nof_goal'))),
            'body_weight' => $customer->getData('body_weight'),
            'target_weight' => $customer->getData('target_weight'),
            'body_height' => $customer->getData('body_height'),
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
        $nutritionGoals = [
            'abnehmen' => [
                'overall' => [
                    0 => [
                        'attr' => 'bmi',
                        'type' => 'customer',
                        'operator' => '>',
                        'value' => $this->_preConstants['safe_bmi_limit'],
                        'error' => [
                            'message' => 'Abnehmen ist nötig nur wenn BMI > '.$this->_preConstants['safe_bmi_limit']
                        ]
                    ]
                ],
                'perDish' => [
                    0 => [
                        'attr' => 'nof_calories',
                        'type' => 'product',
                        'operator' => '<=',
                        'value' => $this->_preConstants['weight_coeff']
                            * $user['body_weight'] * $this->_preConstants['energy_lunch_ratio'],
                        'error' => [
                            'message' => 'zu viel kaloren'
                        ]
                    ]
                ]
            ],
            'zunehmen' => [
                'overall' => [],
                'perDish' => [
                    0 => [
                        'attr' => 'nof_calories',
                        'type' => 'product',
                        'operator' => '<=',
                        'value' => $this->_preConstants['weight_coeff']
                            * $user['body_weight'] * $this->_preConstants['energy_lunch_ratio'],
                        'error' => [
                            'message' => 'zu viel kaloren'
                        ]
                    ]
                ]
            ]
        ];
        return $nutritionGoals[$user['nof_goal']];
//        return $nutritionGoals['zunehmen'];
    }
    /**
     * get work intensity integer value from the top global array
     * @param $optionCode
     * @return mixed
     */
    protected function getWorkIntensityValue($optionCode){
        $key = explode('@',$this->_helper->getCustomerAttrLabel('work_intensity', $optionCode))[1];
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
     * @param array $errors
     * @param bool $isOverall
     * @return string
     */
    protected function generateErrorReport($errors = [], $isOverall = true){
        $modifiedErrors = array_filter($errors);
        if($isOverall){
            $response = [
                'result' => 'incorrect',
                'report' => $modifiedErrors
            ];
            return json_encode($response);
        }
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