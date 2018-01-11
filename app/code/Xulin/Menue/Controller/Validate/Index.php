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
    protected $_isOverallErrorExists = false;
    protected $_isPerDishErrorExists = false;

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
//        $test = '2-SG-1040,2-SG-1021,2-SG-1027,2-SG-234,2-SG-1037,disable,disable,disable,disable,disable,disable,disable,disable,disable,disable';
//        $this->_logger->addDebug(print_r($this->getRequest()->getParam('orders'), true));
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
         * Overall map worker
         * @param $rule
         * @return mixed
         */
        $overallWorker = function ($rule) use ($orders, $user){
            $attrType = $rule['type'];
            if($attrType === 'customer'){
                $leftValue = $user[$rule['attr']];
                $rightValue = round($rule['value'], 2);
                $operator = $rule['operator'];
//                $this->_logger->addDebug(print_r(
//                    'Compare: '.$rule['attr'].
//                    ' Left: '.$leftValue. " Operator: ".$operator ." Right: ".$rightValue
//                    , true));
                $result = $this->getCompareResult($operator, $leftValue, $rightValue);
                if(!$result){
                    $this->_isOverallErrorExists = true;
//                    return $rule['error']['message'];
                    $error = [
                        'attr' => $this->_helper->getCustomerAttrLabel($rule['attr'], false),
                        'error_value' => $leftValue,
                        'operator' => $operator,
                        'required' => $rightValue
                    ];
                    return $error;
                }
            }else{
                /**
                 * todo: if the rule here is not related to a customer attribute
                 * limited amount of a specific food (optional)
                 */
            }
        };
        $product = $this->_productFactory->create();
        /**
         * PerDish map worker
         * @param $productsPerday
         * @return array
         */
        $perDishWorker = function ($productsPerday) use ($user, $goal, $product){
            // load ordered 3 products each day
            $products = array_map(function($sku) use ($product){
                return  $product->loadByAttribute('sku', $sku);
            },$productsPerday);
            $rulesPerDayMap = array_map(function ($rule) use ($products){
                $leftValue = $this->getSumLeftValue($rule['attr'], $products);
                $rightValue = round($rule['value'], 2);
                $operator = $rule['operator'];

//                $this->_logger->addDebug(print_r(
//                    'Compare: '.$rule['attr'].
//                    ' Left: '.$leftValue. " Operator: ".$operator ." Right: ".$rightValue
//                    , true));
                $result = $this->getCompareResult($operator, $leftValue, $rightValue);
                if(!$result){
                    $this->_isPerDishErrorExists = true;
                    $error = [
                        'attr' => $this->_helper->getProductAttrLabel($rule['attr']),
                        'error_value' => $leftValue,
                        'operator' => $operator,
                        'required' => $rightValue
                    ];
                    return $error;
//                    return $rule['error']['message'];
                }
            }, $goal['perDish']);
//            $this->_logger->addDebug(print_r('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', true));
            return array_filter($rulesPerDayMap);
        };
        /**
         * main logic
         */
        if(!empty($goal['overall'])){
            $overallMap = array_map($overallWorker, $goal['overall']);
            if($this->_isOverallErrorExists){
                $error = [
                    'goal' => $user['nof_goal'],
                    'messages' => $overallMap
                ];
//                $this->_logger->addDebug(print_r($error, true));
                return $this->generateErrorReport($error);
            }
        }
        $perDishMap = array_map($perDishWorker, $orders);
        if($this->_isPerDishErrorExists){
            $error = [
                'goal' => $user['nof_goal'],
                'messages' => $perDishMap
            ];
//            $this->_logger->addDebug(print_r($error, true));
            return $this->generateErrorReport($error, false);
        }else{
            $response = [
                'result' => 'correct'
            ];
            return json_encode($response);
        }
//        $response_tmp = [
//            'result' => 'incorrect',
//            'report' => 'Ihre Auswähle passen nicht Ihrem Ernährungsziel! Bitte täglich Einmal Salat'
//        ];
//        return json_encode($response_tmp);
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
     * @return array
     */
    protected function getCustomerInfo(){
        $customer = $this->_customerSession->getCustomer();
        $user = [
            'nof_goal' => strtolower($this->_helper->getCustomerAttrLabel('nof_goal', true, $customer->getData('nof_goal'))),
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
                            'message' => 'Ihr BMI = '.$user['bmi'].'; Abnehmen ist nötig nur wenn BMI > '.$this->_preConstants['safe_bmi_limit'],
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
                            'message' => 'kaloriengehalt <= '
                                .$this->_preConstants['weight_coeff']
                                * $user['body_weight'] * $this->_preConstants['energy_lunch_ratio'].' kcal ist erwünscht.'
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
                        'operator' => '>=',
                        'value' => $this->_preConstants['weight_coeff']
                            * $user['body_weight'] * (1 + 0.2 / 10 * ($user['target_weight']- $user['body_weight']))
                            * $this->_preConstants['energy_lunch_ratio'],
                        'error' => [
                            'message' => 'Kaloriengehalt >='
                            .$this->_preConstants['weight_coeff']
                                * $user['body_weight'] * (1 + 0.2 / 10 * ($user['target_weight']- $user['body_weight']))
                                * $this->_preConstants['energy_lunch_ratio'].'kcal ist erwünscht'
                        ]
                    ]
                ]
            ],
            'gesunde ernährung' => [
                'overall' => [],
                'perDish' => [
                    0 => [
                        'attr' => 'nof_calories',
                        'type' => 'product',
                        'operator' => '>=',
                        'value' => $this->_preConstants['weight_coeff'] * $user['body_weight']
                            * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio'],
                        'error' => [
                            'message'=>  'Kaloriengehalt >='
                                .$this->_preConstants['weight_coeff'] * $user['body_weight']
                                * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio'].' kcal ist erwünscht.'
                        ]
                    ]
                ]
            ],
            'muskelaufbau' => [
                'overall' => [],
                'perDish' => [
                    0 => [
                        'attr' => 'nof_calories',
                        'type' => 'product',
                        'operator' => '>=',
                        'value' => $this->_preConstants['weight_coeff'] * $user['body_weight']
                            * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio'],
                        'error' => [
                            'message' => 'Kaloriengehalt >= '
                            .$this->_preConstants['weight_coeff'] * $user['body_weight']
                                * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio'].' kcal ist erwünscht.'
                        ]
                    ],
                    1 => [
                        'attr' => 'nof_carbs',
                        'type' => 'product',
                        'operator' => '<=',
                        'value' => $this->_preConstants['weight_coeff'] * $user['body_weight']
                            * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio']
                            * $this->_preConstants['keto_nutritional_ratio']['nof_carbs']
                            * $this->_preConstants['calories_grams_rate']['nof_carbs'],
                        'error' => [
                            'message' => 'kohlenhydratgehalt <= '
                            .$this->_preConstants['weight_coeff'] * $user['body_weight']
                                * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio']
                                * $this->_preConstants['keto_nutritional_ratio']['nof_carbs']
                                * $this->_preConstants['calories_grams_rate']['nof_carbs'].' g ist erwünscht.'
                        ]
                    ],
                    2 => [
                        'attr' => 'nof_protein',
                        'type' => 'product',
                        'operator' => '>=',
                        'value' => $this->_preConstants['weight_coeff'] * $user['body_weight']
                            * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio']
                            * $this->_preConstants['keto_nutritional_ratio']['nof_protein']
                            * $this->_preConstants['calories_grams_rate']['nof_protein'],
                        'error' => [
                            'message' => 'Proteingehalt >= '
                            .$this->_preConstants['weight_coeff'] * $user['body_weight']
                                * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio']
                                * $this->_preConstants['keto_nutritional_ratio']['nof_protein']
                                * $this->_preConstants['calories_grams_rate']['nof_protein'].' g ist erwünscht.'
                        ]
                    ],
                    3 => [
                        'attr' => 'nof_fat',
                        'type' => 'product',
                        'operator' => '>=',
                        'value' => $this->_preConstants['weight_coeff'] * $user['body_weight']
                            * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio']
                            * $this->_preConstants['keto_nutritional_ratio']['nof_fat']
                            * $this->_preConstants['calories_grams_rate']['nof_fat'],
                        'error' => [
                            'message' => 'Fettgehalt >= '
                            .$this->_preConstants['weight_coeff'] * $user['body_weight']
                                * $user['work_intensity'] * $this->_preConstants['energy_lunch_ratio']
                                * $this->_preConstants['keto_nutritional_ratio']['nof_fat']
                                * $this->_preConstants['calories_grams_rate']['nof_fat'].' g ist erwünscht.'
                        ]
                    ]
                ]
            ]
        ];
        return $nutritionGoals[$user['nof_goal']];
//        return $nutritionGoals['abnehmen'];
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
     * @param bool $isOverall
     * @return string
     */
    protected function generateErrorReport($error = [], $isOverall = true){
        $response = [
            'result' => 'incorrect',
            'goal' => $error['goal'],
            'type' => $isOverall? 'overall' : 'perdish',
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