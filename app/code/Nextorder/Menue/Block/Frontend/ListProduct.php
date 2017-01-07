<?php
namespace Nextorder\Menue\Block\Frontend;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct{

    protected $_customProductCollection;
    protected $_productFactory;
    public $_price_class;
    public $_menu_index;
    protected $_logger;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory, //product Factory injection
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_customProductCollection = $productCollection;
        $this->_productFactory = $productFactory->create();
        $this->_logger = $logger;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }
    /*
     * set price class
     */
    public function setPriceClass($price_class){
        return $this->_price_class = $price_class;
    }
    /*
     * set menu index to locate changed menu
     */
    public function setMenuIndex($menu_index){
        return$this->_menu_index = $menu_index;
    }
    /*
    * get custom product collection
    */
    public function getCustomCollection(){
        $this->_logger->addDebug(print_r($this->getIdAndOptionSkus('inc','optionSkus.txt'),true));
        $productCollection = $this->_customProductCollection->create()
            ->addAttributeToFilter('sku',array('in' => $this->getIdAndOptionSkus('inc','optionSkus.txt')))
            ->addAttributeToFilter('price_class',$this->_price_class);
        return $productCollection;
    }
    /*
    * get single product object from the custom collection
    */
    public function getCustomSingleProduct($sku){
     return  $this->_productFactory->loadByAttribute('sku', $sku);
    }
    /*
  * get related Id and Option id according to sku
  */
    public function getIdAndOptionSkus($dir, $file){
        $serializedArray = file_get_contents($this->df_module_dir("Nextorder_Menue")."/".$dir."/".$file);
        return unserialize($serializedArray);
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
}