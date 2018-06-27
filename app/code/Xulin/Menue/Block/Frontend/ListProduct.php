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
    protected $_allowedSkus;
    /**
     * ListProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Nextorder\MenuData\Model\MenudataFactory $modelMenudataFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Nextorder\Menue\Helper\Data $helper
     * @param array $data
     */
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
    /**
     * @param int $price_class
     * @return int
     */
    public function setPriceClass($price_class){
        return $this->_price_class = $price_class;
    }
    /**
     * @param int $menu_index
     * @return int
     */
    public function setMenuIndex($menu_index){
        return $this->_menu_index = $menu_index;
    }
    /**
     * @param $optionIdIndex
     * @return mixed
     */
    public function setOptionIdIndex($optionIdIndex){
        return $this->_optionIdIndex = $optionIdIndex;
    }

    public function setCurrentUserStatus($currentUserStatus = array()){
        return $this->_currentUserStatus = $currentUserStatus;
    }
    /**
     * @return array
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
                $this->_allowedSkus = implode(',', $arrayToFilter);
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
                $this->_allowedSkus = implode(',', $arrayToFilter);
                $productCollection = $this->_customProductCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
                break;
            case "LOGIN_WITHOUT_WP":
                $talentSkus = $action["talent_skus"];
                $this->_allowedSkus = implode(',', $talentSkus);
                $productCollection = $this->_customProductCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sku', array('in' => $talentSkus));
                break;
            default:
                $productCollection = $this->loadLocalOptions();
                break;
        }
//        $this->_logger->addDebug(print_r($this->_allowedSkus, true));
        return [
            'collection' => $productCollection,
            'skus' => $this->_allowedSkus
        ];
    }

    /**
     * @return $this
     */
    public function loadLocalOptions(){
        $arrayToFilter = array_keys($this->_helper->getSerializedData('inc','bundleDataSource.txt')[$this->_optionIdIndex]);
        $this->_allowedSkus = implode(',', $arrayToFilter);
        $productCollection = $this->_customProductCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
        return $productCollection;
    }

//    public function getCustomSingleProduct($sku){
//        return  $this->_productFactory->loadByAttribute('sku', $sku);
//    }
}