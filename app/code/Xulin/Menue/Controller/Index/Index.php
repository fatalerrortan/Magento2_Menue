<?php

namespace Nextorder\Menue\Controller\Index;

use Magento\Framework\App\Action\Context;
//In Magento 2 every action has its own class which implements the execute() method.

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_menueFactory;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory){

        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute(){

        // load custom helper in Controller
//        $test = $this->_objectManager->create('Nextorder\Menue\Helper\Data')->testHelper();
        // load custom model in Controller
//        $testObj = $this->_objectManager->create('Nextorder\Menue\Model\Menue');
//        $testObj->setLabel('test4 ');
//        $testObj->setValue(345);
//        $testObj->save();
//        $testObj = $this->_objectManager->create('Nextorder\Menue\Model\Menue');
//        $collection = $testObj->getCollection()->addFieldToFilter('label', array('like'=> 'test3'));
//        foreach ($collection as $item){
//            var_dump($item->getData());
//        }
        // load custom model using factory
//        $testCollection = $this->_menueFactory->create()->getCollection();
//        foreach($testCollection as $item){
//            var_dump($item->getData());
//        }
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}