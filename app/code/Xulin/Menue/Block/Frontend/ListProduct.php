<?php
namespace Nextorder\Menue\Block\Frontend;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct{

    protected $_customProductCollection;
//    protected $_productFactory;
    public $_price_class;
    public $_menu_index;
    public $_optionIdIndex;
    public $_currentUserStatus;
    public $_logger;
    protected $_customerSession;
    protected $_modelMenudataFactory;
    public $_helper;
    public $_in_stock;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Nextorder\MenuData\Model\MenudataFactory $modelMenudataFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
//        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Psr\Log\LoggerInterface $logger,
        \Nextorder\Menue\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_modelMenudataFactory = $modelMenudataFactory;
        $this->_customProductCollection = $productCollection;
//        $this->_productFactory = $productFactory->create();
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

    public function setCurrentUserStatus($currentUserStatus = array()){
        return $this->_currentUserStatus = $currentUserStatus;
    }
    /*
    * get custom product collection
    */
    public function getCustomCollection(){
        $action = $this->_currentUserStatus;
//        $this->_logger->addDebug(print_r($action, true));
//        $arrayToFilter = array();
        switch ($action['type']) {
            case "NO_LOGIN":
                $productCollection = $this->loadLocalOptions();
                break;
            case "FIRST_LOGIN_WITHOUT_WP":
                $productCollection = $this->loadLocalOptions();
                break;
            case "FIRST_LOGIN_WITH_WP":
                $remoteSkus = $action["remote_skus"]['all'];
                $optionSkus = $action["option_skus"];
                $this->_in_stock = $action["remote_skus"]['in_stock'];
                $arrayToFilter = array_intersect($remoteSkus, $optionSkus);
                $productCollection = $this->_customProductCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
                break;
            case "LOGIN_WITH_WP":
                $remoteSkus = $action["remote_skus"]['all'];
                $this->_in_stock = $action["remote_skus"]['in_stock'];
                $talentSkus = $action["talent_skus"];
                $optionSkus = $action["option_skus"];
                $arrayToFilter = array_intersect($remoteSkus, $talentSkus);
                if(empty($arrayToFilter)){
                    $arrayToFilter = array_intersect($remoteSkus, $optionSkus);
                }
                $productCollection = $this->_customProductCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
                break;
            case "LOGIN_WITHOUT_WP":
                $talentSkus = $action["talent_skus"];
                $productCollection = $this->_customProductCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sku', array('in' => $talentSkus));
                break;
            default:
                $productCollection = $this->loadLocalOptions();
                break;
        }
        return $productCollection;
    }

    public function loadLocalOptions(){
        $arrayToFilter = array_keys($this->_helper->getSerializedData('inc','bundleDataSource.txt')[$this->_optionIdIndex]);
        $productCollection = $this->_customProductCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
        return $productCollection;
    }

//    public function getCustomSingleProduct($sku){
//        return  $this->_productFactory->loadByAttribute('sku', $sku);
//    }
}