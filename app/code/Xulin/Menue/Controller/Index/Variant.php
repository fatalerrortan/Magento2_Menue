<?php


namespace Nextorder\Menue\Controller\Index;

use Magento\Framework\App\Action\Context;
//In Magento 2 every action has its own class which implements the execute() method.

class Variant extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory){

        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute(){
        //get Params form Post Method
//        $this->getRequest()->getPost();
        // load custom helper in Controller
//        $test = $this->_objectManager->create('Nextorder\Menue\Helper\Data')->testHelper();
//        echo $this->_helper->getAdminConfig()[1];
        echo 'test!!!';
        $resultPage = $this->_resultPageFactory->create();
//        return $resultPage;
        $Muskelaufbau = [
            0 => [
                'item' => 'rindfleisch',
                'amount' => 3
            ],
            1 => [
                'item' => 'eier',
                'amount' => 2
            ],
            2 => [
                'item' => 'salat',
                'amount' => 2
            ]
        ];
    }
}