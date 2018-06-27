<?php

namespace Nextorder\Menue\Controller\Index;

use Magento\Framework\App\Action\Context;
//In Magento 2 every action has its own class which implements the execute() method.

class Index extends \Magento\Framework\App\Action\Action{
    protected $_resultPageFactory;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory){

        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute(){
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}