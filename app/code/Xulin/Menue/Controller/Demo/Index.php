<?php
namespace Nextorder\Menue\Controller\Demo;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action{
    protected $_productFactory;
    protected $_customerSession;
    protected $_nGoalsFactory;
    protected $_helper;

    public function __construct(Context $context,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Customer\Model\Session $customerSession,
                                \Nextorder\Menue\Model\NgoalsFactory $nGoalsFactory,
                                \Nextorder\Menue\Helper\Data $helper){

        $this->_productFactory = $productFactory;
        $this->_customerSession = $customerSession;
        $this->_nGoalsFactory = $nGoalsFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute(){
        $customer = $this->_customerSession->getCustomer();
        $nutritionLabel = $this->_helper
            ->getCustomerAttrLabel('nof_goal', true, $customer->getData('nof_goal'));
        $nutritionModel = $this->_nGoalsFactory->create();
        $nutritionObj = $nutritionModel->getCollection()->addFieldToFilter('label', $nutritionLabel)->getData()[0];
        $jsonDef = $nutritionObj['def'];
        $user = ['body_weight' => 85];
        $arrayDef = json_decode($jsonDef, true);
        $value = null;
        eval("\$value=".$arrayDef['singlerule'][0]['value'].";");
        $arrayDef['singlerule'][0]['value'] = $value;
        echo $value;
    }

    /**
     * Algorithm Skeleton
     */
    protected function nutritionAlgorithm($orders, $user, $goal){
        /**
         * logic to implement
         */
        return 'Algorithms Result';
    }
}