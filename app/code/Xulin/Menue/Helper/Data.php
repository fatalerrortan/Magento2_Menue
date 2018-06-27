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
    protected $_nGoalsFactory;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Nextorder\Menue\Model\NgoalsFactory $ngoalsFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_eavAttributeRepository = $eavAttributeRepository;
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_logger = $logger;
        $this->_nGoalsFactory = $ngoalsFactory->create();
    }
    /** get extension configuration from admin/stores/configurations/nextorder/Wochenmenü
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
     * get extension configuration from admin/stores/configurations/nextorder/Ernährungsziele
     * @return array
     */
    public function getDefAttrs(){
        return [
            'overall' => $this->_scopeConfig->getValue('ngoal/ngoal_group_1/ngoal_group_1_field_1'),
            'daily' => $this->_scopeConfig->getValue('ngoal/ngoal_group_1/ngoal_group_1_field_2')
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
    /** get customer attribute options label
     * @param null $optionId | true => get label of a single goal
     * @return array|mixed
     */
    public function getCustomerAttrLabel($attrCode, $withOptions = false, $optionId = null){
        $attributes = $this->_eavAttributeRepository->get(Customer::ENTITY, $attrCode);
        if(!$withOptions){return $attributes->getDefaultFrontendLabel();}
        $options = $attributes->getSource()->getAllOptions(false);
        $goalAttrs = [];
        foreach ($options as $option) {
//            $this->_logger->addDebug(print_r($option, true));
            $goalAttrs[$option['value']] = $option['label'];
        }
        if(empty($optionId)){return $goalAttrs;}
        return $goalAttrs[$optionId];
    }
    /**
     * get nutrition goal definitions which are defined in admin/products/Ernährungsziele
     * @return array
     */
    public function getGoalDefinitions(){
        $defs = [];
        $goalLabels = $this->getCustomerAttrLabe('nof_goal');
        $nGoalsModel = $this->_nGoalsFactory;
        foreach ($goalLabels as $label){
            $nGoalsCollection = $nGoalsModel->getCollection();
            $defs[$label] = $nGoalsCollection->addFieldToSelect('*')->addFieldToFilter('goal', $label)->load()->getData();
        }
//        $this->_logger->addDebug(print_r($defs, true));
        return $defs;
    }
    /** get product attribute label
     * @param $attrCode
     * @param bool $withOptions | true => get related option labels if the target attr was defined as a select
     * @param bool $optionIds | true => get single option label by option id
     * @return array|null|string
     */
    public function getProductAttrLabel($attrCode, $withOptions = false, $optionIds = false){
        $labels = [];
        $modiAttrOptions = [];
        if(!$withOptions){
            return $this->_productAttributeRepository->get($attrCode)->getDefaultFrontendLabel();
        }
        $attrOptions = $this->_productAttributeRepository->get($attrCode)->getOptions();
        foreach ($attrOptions as $option){
            $modiAttrOptions[$option['value']] = $option['label'];
        }
        if(!$optionIds){return $modiAttrOptions;}
        $optionIds = explode(",", $optionIds);
        foreach ($optionIds as $id){
            $labels[] = $modiAttrOptions[$id];
        }
        return $labels;
    }

    public function getNutritionGoalWithString($goal){
        $nutritionObj = $this->_nGoalsFactory->getCollection()->addFieldToFilter('label', $goal)->getData()[0];
        $jsonDef = $nutritionObj['def'];
        $arrayDef = json_decode($jsonDef, true);
        return $arrayDef;
    }
}