<?php
namespace Nextorder\Menue\Block\Frontend;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct{

    protected $_customProductCollection;
    protected $_productFactory;
    public $_price_class;
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
    * get custom product collection
    */
    public function getCustomCollection(){
        $productCollection = $this->_customProductCollection->create()->addAttributeToFilter('price_class',$this->_price_class);
        $this->_logger->addDebug("!!TEST!!!!!!".$this->_price_class);
        return $productCollection;
    }
    /*
    * get single product object from the custom collection
    */
    public function getCustomSingleProduct($sku){
     return  $this->_productFactory->loadByAttribute('sku', $sku);
    }
}