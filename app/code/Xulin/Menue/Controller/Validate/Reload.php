<?php


namespace Nextorder\Menue\Controller\Validate;

use Magento\Framework\App\Action\Context;

class Reload extends \Magento\Framework\App\Action\Action{

    protected $_resultPageFactory;
    protected $_logger;

    public function __construct(Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                                \Psr\Log\LoggerInterface $logger){
        $this->_resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute(){
        $resultPage = $this->_resultPageFactory->create();
        $reloadHtml = $resultPage->getLayout()->getBlock('ListReload')->toHtml();
        $this->_logger->addDebug(print_r($reloadHtml, true));
        $this->getResponse()->setBody($reloadHtml);
    }
}