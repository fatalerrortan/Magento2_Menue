<?php
/**
 * Created by PhpStorm.
 * User: fatalerrortxl
 * Date: 23.12.16
 * Time: 15:15
 */
namespace Nextorder\Menue\Helper;
use  Magento\Customer\Model\Customer;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{
    protected $_scopeConfig;
    protected $_eavAttributeRepository;
    protected $_productAttributeRepository;
    protected $_logger;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_eavAttributeRepository = $eavAttributeRepository;
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_logger = $logger;
    }
    /**
     * @return array
     */
    public function getAdminConfig(){
        return [
                'main' => array(
                    $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_1'),
                    $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_2'),
                    $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_3'),
                    $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_4'),
                    $this->_scopeConfig->getValue('menu/menu_group_2/menu_group_2_field_5')
                ),
                'side' => array(
                    $this->_scopeConfig->getValue('menu/menu_group_3/menu_group_3_field_1'),
                    $this->_scopeConfig->getValue('menu/menu_group_3/menu_group_3_field_2'),
                    $this->_scopeConfig->getValue('menu/menu_group_3/menu_group_3_field_3'),
                    $this->_scopeConfig->getValue('menu/menu_group_3/menu_group_3_field_4'),
                    $this->_scopeConfig->getValue('menu/menu_group_3/menu_group_3_field_5')
                )
        ];
    }
    /**
     * @return array
     */
    public function getBundleProductSku(){
        return $this->_scopeConfig->getValue('menu/menu_group_1/menu_group_1_field_1');
    }

    /**
     * @param string $dir
     * @param string $file
     * @return array mixed
     */
    public function getSerializedData($dir, $file){
        $serializedArray = file_get_contents($this->df_module_dir("Nextorder_Menue")."/".$dir."/".$file);
        return unserialize($serializedArray);
    }

    /**
     * @param string $moduleName
     * @param string $type
     * @return string
     */
    public  function df_module_dir($moduleName, $type = '') {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $reader = $om->get('Magento\Framework\Module\Dir\Reader');
        return $reader->getModuleDir($type, $moduleName);
    }

    public function getGoalDefinition($optionId = null){
        $attributes = $this->_eavAttributeRepository->get(Customer::ENTITY, 'nof_goal');
        $options = $attributes->getSource()->getAllOptions(false);
        $label = null;
        if(empty($optionId)){return $options;}
        foreach ($options as $option){
            if($option['value'] === $optionId){
                $label = $option['label'];
                break;
            }
        }
        $Muskelaufbau = [
            0 => [
                'item' => 'rindfleisch',
                'orderType' => 'mainOrder',
                'amount' => 3
            ],
            1 => [
                'item' => 'eier',
                'orderType' => 'mainOrder',
                'amount' => 2
            ],
            2 => [
                'item' => 'salat',
                'orderType' => 'sideOrder',
                'amount' => 2
            ]
        ];
        return $Muskelaufbau;
    }

    /**
     * @param string $optionIds
     * @param string $attrCode
     * @return array
     */
    public function getProductAttrLabel($optionIds, $attrCode){
        $labels = [];
        $modiAttrOptions = [];
        $optionIds = explode(",", $optionIds);
        $attrOptions = $this->_productAttributeRepository->get($attrCode)->getOptions();
        foreach ($attrOptions as $option){
            $modiAttrOptions[$option['value']] = $option['label'];
        }
        foreach ($optionIds as $id){
            $labels[] = $modiAttrOptions[$id];
        }
    return $labels;
    }
}