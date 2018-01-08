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
//    protected $_productAttributeRepository;
//    protected $_customerRepository;
//    protected $_eavAttributeRepository;
    protected $_helper;
    protected $_productCollectionFactory;
    protected $_preConstants = array(
        'weight_coeff' => 24,
        'energy_lunch_ratio' => 0.4,
        'safe_bmi_limit' => 25,
        'keto_nutritional_ratio' =>
            ['nof_carbs' => 0.25, 'nof_protein' => 0.4, 'nof_fat' => 0.35],
        'calories_grams_rate' =>
            ['nof_carbs' => 0.25, 'nof_protein' => 0.25, 'nof_fat' => 0.11],
        'work_intensity' =>
            ['sit' => 1.4, 'stand' => 1.6, 'walk' => 1.8, 'hard' => 2]
    );
    public $_error = array();

    public function __construct(Context $context,
                                \Magento\Customer\Model\Session $customerSession,
//                                \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
//                                \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
//                                \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
                                \Psr\Log\LoggerInterface $logger,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Nextorder\Menue\Helper\Data $helper){
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
//        $this->_customerRepository = $customerRepository;
//        $this->_productAttributeRepository = $productAttributeRepository;
//        $this->_eavAttributeRepository = $eavAttributeRepository;
        $this->_helper = $helper;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    public function execute(){
        // construct Argument 1: menu orders
        $orders = $this->getOrdersModified($this->getRequest()->getParam('orders'));
        // construct Argument 2: user info
        $user = $this->getCustomerInfo();
        // construct argument 3: nutrition goal definition
        $goal = $this->getNutritionGoal($user);
        $result = $this->nutritionAlgorithm($orders, $user, $goal);
        echo $result;
    }
    /**
     * @param $orders
     * @param $user
     * @param $goals
     * @return string
     */
    protected function nutritionAlgorithm($orders, $user, $goals){

        /**
         *
         *
         * todo: algorithm logic
         *
         */

        $response = [
            'result' => 'incorrect',
            'message' => 'Ihre Auswähle passen nicht Ihrem Ernährungsziel! Bitte täglich Einmal Salat'
        ];
        return json_encode($response);
    }
    /**
     * get modified orders structure for the first argument of the nutrition algorithm
     * @param array $orders
     * @return array
     */
    protected function getOrdersModified($orders = []){
        $orders = array_chunk(explode(',', $orders), 5);
        $modiOrders = [
            'mon' => array_column($orders, 0),
            'tue' => array_column($orders, 1),
            'wed' => array_column($orders, 2),
            'thu' => array_column($orders, 3),
            'fri' => array_column($orders, 4),
        ];
        return $modiOrders;
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
                            'message' => 'Just For Test'
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
                            'message' => 'Just For Test'
                        ]
                    ]
                ]
            ],
        ];
        return $nutritionGoals[$user['nof_goal']];
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
        return $weight / pow($height, 2);
    }
    /**
     * @param $skusToFilter
     * @return $this
     */
    protected function getProductCollection($skusToFilter){
        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')->addAttributeToFilter('sku', array('in' => $skusToFilter))->load();
        return $productCollection;
    }
}