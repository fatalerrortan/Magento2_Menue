<?php
namespace Nextorder\Menue\Block\Frontend;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    protected $_customProductCollection;
    protected $_productFactory;
    public $_price_class;
    public $_menu_index;
    public $_optionIdIndex;
    protected $_logger;
    protected $_customerSession;
    protected $_modelMenudataFactory;
    public $_helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Nextorder\MenuData\Model\MenudataFactory $modelMenudataFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory, //product Factory injection
        \Psr\Log\LoggerInterface $logger,
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_modelMenudataFactory = $modelMenudataFactory;
        $this->_customProductCollection = $productCollection;
        $this->_productFactory = $productFactory->create();
        $this->_logger = $logger;
        $this->_helper = $helper;
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
        return $this->_menu_index = $menu_index;
    }

    public function setOptionIdIndex($optionIdIndex){
        return $this->_optionIdIndex = $optionIdIndex;
    }
    /*
    * get custom product collection
    */
    public function getCustomCollection(){
//        $this->_logger->addDebug(print_r($this->getIdAndOptionSkus('inc','optionSkus.txt'),true));
        if ($this->_customerSession->isLoggedIn())
        {
            $menudataModel = $this->_modelMenudataFactory->create();
            $customerMenu = $menudataModel->getMenuDataByCustomerId($this->_customerSession->getCustomerId())->getData();
            switch ($this->_menu_index) {
                case 1:
                    $skus = explode(",",$customerMenu['product_mon']);
                    break;
                case 2:
                    $skus = explode(",",$customerMenu['product_tue']);
                    break;
                case 3:
                    $skus = explode(",",$customerMenu['product_wed']);
                    break;
                case 4:
                    $skus = explode(",",$customerMenu['product_thu']);
                    break;
                case 5:
                    $skus = explode(",",$customerMenu['product_fri']);
                    break;
            }
            $productCollection = $this->_customProductCollection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', array('in' => $skus));
            /* bei erstem Login ist Collection noch empty */
            if (empty($productCollection)) {
                $productCollection = $this->_customProductCollection->create()
                    ->addAttributeToFilter('sku', array('in' => $this->getIdAndOptionSkus('inc', 'optionSkus.txt')))
                    ->addAttributeToFilter('price_class', $this->_price_class);
            }
        } else {

         $arrayToFilter= array_keys($this->_helper->getSerializedData('inc','bundleDataSource.txt')[$this->_optionIdIndex]);
            $productCollection = $this->_customProductCollection->create()
                ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
//                ->addAttributeToFilter('price_class', $this->_price_class);
        }
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