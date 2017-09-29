<?php
namespace Nextorder\Menue\Block\Frontend;

//use Magento\Framework\Validator\Exception;

class LiveListProduct extends \Magento\Catalog\Block\Product\ListProduct{

    public $_logger;
    public $_in_stock;
    protected $_liveSkus;
    protected $_customProductCollection;
//    protected $_productFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Psr\Log\LoggerInterface $logger,
//        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->_customProductCollection = $productCollection;
//        $this->_productFactory = $productFactory->create();
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    public function setLiveSkus($skus){
        $this->_liveSkus = explode(",", $skus);
    }

    public function getCustomCollection(){
        $arrayToFilter = $this->_liveSkus;
        $productCollection = $this->_customProductCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', array('in' => $arrayToFilter));
        $this->_logger->addDebug(print_r('block works', true));
        return $productCollection;
    }

//    public function getCustomSingleProduct($sku){
//     return  $this->_productFactory->loadByAttribute('sku', $sku);
//    }
}