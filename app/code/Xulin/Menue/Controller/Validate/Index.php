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
        $result = $this->goalCheck($mainOrderSkus, $sideOrderSkus, '');
        echo $result;
    }

    protected function goalCheck($mainOrderSkus, $sideOrderSkus = null, $goals){
        $response = [
            'result' => 'incorrect',
            'message' => 'Ihre Auswähle passen nicht Ihrem Ernährungsziel! Bitte täglich Einmal Salat'
        ];
        return json_encode($response);
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