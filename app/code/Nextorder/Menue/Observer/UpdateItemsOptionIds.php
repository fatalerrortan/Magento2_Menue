<?php

namespace Nextorder\Menue\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateItemsOptionIds implements ObserverInterface{

    protected $_logger;
    protected $_selectionIds;
    protected $_selectionSkus;


    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_logger = $logger;
    }
/*
 * read and update all children products from the bundle product"weekend menu"
 * save in module Nextorder_Menue => inc
 */
    public function execute(\Magento\Framework\Event\Observer $observer){

        $selectionIds = array();
        $selectionSkus = array();
        $product = $observer->getProduct();
        if($product->getSku() != "test_bundle"){return true;}
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        foreach ($selectionCollection as $proselection) {
            $item = array(
                "product_id" => $proselection->getProductId(),
                "option_id" => $proselection->getSelectionId()
            );
            $selectionIds[$proselection->getSku()] = $item;
            $selectionSkus[] = $proselection->getSku();
        }
        $selectionIds[$product->getSku()] = array(
            "product_id" => $product->getId(),
            "option_id" => ""
        );
        $this->_selectionIds = $selectionIds;
        $this->_selectionSkus = $selectionSkus;
        if($this->save('inc','optionIds.txt') && $this->save('inc','optionSkus.txt', true)){return true;}
//        $this->_logger->addDebug($this->df_module_dir("Nextorder_Menue"));
    }
    /*
     * get module dir to save serialized array of option ids
     */
    public  function df_module_dir($moduleName, $type = '') {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $reader = $om->get('Magento\Framework\Module\Dir\Reader');
        return $reader->getModuleDir($type, $moduleName);
    }
    /*
     * check and generate Order "inc" to set serialized array of option ids
     */
    public function save($dir, $file, $flag = false){
        $moduleDir = $this->df_module_dir("Nextorder_Menue")."/".$dir;
        if(!is_dir($moduleDir)){
           mkdir($moduleDir,0777);
       }
        if($flag == true){file_put_contents($moduleDir."/".$file, serialize($this->_selectionSkus));}
        else{file_put_contents($moduleDir."/".$file, serialize($this->_selectionIds));}
        return true;
    }
}