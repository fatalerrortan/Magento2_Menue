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
    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Nextorder\Menue\Helper\Data $helper
     */
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
        $mainOrderSkus =$this->getRequest()->getParam('mainOrders');
        $sideOrderSkus =$this->getRequest()->getParam('sideOrders');

        $goalOptionId = $this->_customerSession->getCustomer()->getData('nof_goal');
        $userGoals = $this->_helper->getGoalDefinition($goalOptionId);
        $result = $this->goalCheck($mainOrderSkus, $sideOrderSkus, $userGoals);
        echo $result;
    }

    protected function goalCheck($mainOrderSkus, $sideOrderSkus = null, $goals){
        $mainOrders = $this->getProductCollection($mainOrderSkus);
        if(!empty($sideOrderSkus)){
            $sideOrders = $this->getProductCollection($sideOrderSkus);
        }
        $this->_logger->addDebug(print_r($goals, true));

//        foreach ($goals as $goal){
//
//        }

        foreach ($mainOrders as $order){
            $this->_logger->addDebug(print_r($this->_helper->getProductAttrLabel($order->getData('nof_animalproducts'), 'nof_animalproducts'), true));
            $this->_logger->addDebug(print_r($this->_helper->getProductAttrLabel($order->getData('nof_cropproducts'), 'nof_cropproducts'), true));

//            $this->_logger->addDebug(print_r($order->getData('nof_processedfoods'), true));
            $this->_logger->addDebug(print_r("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!", true));
        }
//        $this->_helper->getProductAttrLabel(null, 'nof_animalproducts');
        return 'incorrect';
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