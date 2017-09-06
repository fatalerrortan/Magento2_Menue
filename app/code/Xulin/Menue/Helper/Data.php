<?php
/**
 * Created by PhpStorm.
 * User: fatalerrortxl
 * Date: 23.12.16
 * Time: 15:15
 */
namespace Nextorder\Menue\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{
    protected $_scopeConfig;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->_scopeConfig = $scopeConfig;
    }

    public function getAdminConfig(){
        return array(
            $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_1'),
            $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_2'),
            $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_3'),
            $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_4'),
            $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_5')
        );
    }

    public function getBundleProductSku(){
        return $this->_scopeConfig->getValue('menu/menu_group_1/menu_group_1_field_1');
    }

    public function getSerializedData($dir, $file){
        $serializedArray = file_get_contents($this->df_module_dir("Nextorder_Menue")."/".$dir."/".$file);
        return unserialize($serializedArray);
    }

    public  function df_module_dir($moduleName, $type = '') {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $reader = $om->get('Magento\Framework\Module\Dir\Reader');
        return $reader->getModuleDir($type, $moduleName);
    }
}