<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Order extends \Magento\Framework\App\Action\Action{

    protected $_productRepository;
    protected $_logger;
    protected $_customerSession;
    protected $_orderFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Nextorder\Subaccounts\Model\OrderFactory $orderFactory
    ){
        $this->_productRepository = $productRepository;
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }

    public function execute(){
        $begin = $this->getRequest()->getParam("begin");
        $end = $this->getRequest()->getParam("end");
        $orderCollection = $this->_orderFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => array($this->_customerSession->getCustomerId())));
//            ->addFieldToFilter('created_at', array('lt' => array($begin)));
        foreach ($orderCollection as $order){
            $this->_logger->addDebug(print_r($order->getData(), true));
        }
    }
}