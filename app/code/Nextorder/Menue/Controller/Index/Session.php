<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Session extends \Magento\Framework\App\Action\Action{

    protected $_logger;
    protected $_customerSession;
//    protected $_resultJsonFactory;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Session\SessionManagerInterface $customerSession
    ){
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute(){
        if ($this->getRequest()->getParam("user_choose")){
//            $menu_orders_skus = explode(",", $this->getRequest()->getParam("user_choose"));
            $_SESSION['user_choose'] = $this->getRequest()->getParam("user_choose");
//            $this->_customerSession->setUserOptionContainer($this->getRequest()->getParam("user_choose"));
//            echo $this->_customerSession->getUserOptionContainer();
        }
    }
}