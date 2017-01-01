<?php

namespace Nextorder\Menue\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateItemsOptionIds implements ObserverInterface{

    protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){

        $selectionIds = array();
        $product = $observer->getProduct();
//        $this->_logger->addDebug("!!!!!!!!!!!!!!!!!!!!!".$_product->getData('type_id'));
        if($product->getSku() != "test_bundle"){return true;}
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        foreach ($selectionCollection as $proselection) {
            $selectionIds[$proselection->getProductId()] = $proselection->getSelectionId();
        }
    }
}