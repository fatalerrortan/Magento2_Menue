<?php
namespace Nextorder\Menue\Block\Frontend;

class ListReload extends \Magento\Catalog\Block\Product\ListProduct{

    protected $_customerSession;
    public $_helper;
    protected $_customCollection;
    protected $_logger;
    public $_in_stock;
    public $_menu_index;


    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $customCollection,
        \Psr\Log\LoggerInterface $logger,
        \Nextorder\Menue\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_customCollection = $customCollection;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    public function getCustomCollection(){
        $conditions = json_decode($this->getRequest()->getParam('conditions'));
        $this->_menu_index = $conditions->index;
        $skusString = $this->getRequest()->getParam('skus');
        $skus = explode(',', $skusString);

        $listCollection = $this->_customCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => ['2-SG-1021','2-SG-1040']));

//        $this->_logger->addDebug(print_r($conditions, true));
//        $this->_logger->addDebug(print_r($skus, true));

        return [
            'collection' => $listCollection,
            'skus' => $skusString
        ];
    }

}