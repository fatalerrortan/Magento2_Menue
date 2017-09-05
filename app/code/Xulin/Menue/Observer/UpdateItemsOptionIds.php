<?php

namespace Nextorder\Menue\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateItemsOptionIds implements ObserverInterface{

    protected $_logger;
    protected $_toCartConfig;
    protected $_bundleSKus;
    protected $_scopeConfig;
    protected $_bundleDataSource;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
    }
/*
 * read and update all children products from the bundle product"weekend menu"
 * save in module Nextorder_Menue => inc
 */
    public function execute(\Magento\Framework\Event\Observer $observer){

//        $configArray = array();
//        $bundleSkus = array();


        $bundleDataSource = array();

        $product = $observer->getProduct();
        if($product->getSku() != $this->_scopeConfig->getValue('menu/menu_group_1/menu_group_1_field_1')){return true;}
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        foreach ($selectionCollection as $proselection) {
//            $item = array(
//                "selection_id" => $proselection->getData('selection_id'),
//                "option_id" => $proselection->getData('option_id')
//            );
//            $bundleSkus[] = $proselection->getSku();
//            $configArray[$proselection->getSku()] = $item;

            $selectionID = $proselection->getData('selection_id');
            $optionID = $proselection->getData('option_id');
            $sku = $proselection->getSku();
            $bundleDataSource[$optionID][$sku] = $selectionID;
        }
        $this->_bundleDataSource = $bundleDataSource;
//        $configArray[$product->getSku()] = $product->getId();
//        $this->_toCartConfig = $configArray;
//        $this->_bundleSKus = $bundleSkus;
//
//        $this->_logger->addDebug(print_r($configArray, true));
//        $this->_logger->addDebug(print_r($bundleSkus, true));
//
//        if($this->save('inc','toCartConfig.txt') && $this->save('inc','optionSkus.txt', true)){return true;}
        return $this->save('inc','bundleDataSource.txt');
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
        file_put_contents($moduleDir."/".$file, serialize($this->_bundleDataSource));
//       if($flag){
//           file_put_contents($moduleDir."/".$file, serialize($this->_bundleSKus));
//       }else{
//           file_put_contents($moduleDir."/".$file, serialize($this->_toCartConfig));
//       }
        return true;
    }
}