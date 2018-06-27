<?php


namespace Nextorder\Menue\Controller\Index;

class Order extends \Magento\Framework\App\Action\Action{

    protected $_resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ){
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute(){
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}