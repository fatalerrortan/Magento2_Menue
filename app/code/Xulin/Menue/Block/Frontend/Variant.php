<?php

namespace Nextorder\Menue\Block\Frontend;

class Variant extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Nextorder\MenuData\Model\MenudataFactory
     */
    protected $_modelMenudataFactory;

    //protected $_logger;
    public $_helper;
    protected $_productCollection;
    protected $_productFactory;
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        //\Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection, // product collection injection
        \Magento\Catalog\Model\ProductFactory $productFactory, //product Factory injection
        \Magento\Customer\Model\Session $customerSession,
        MenudataFactory $modelMenudataFactory,
        array $data = []
    )
    {
        $this->_helper = $helper;
        //$this->_logger = $logger;
        $this->_productCollection = $productCollection->create();
        $this->_productFactory = $productFactory->create();
        $this->_customerSession = $customerSession;
        $this->_modelMenudataFactory = $modelMenudataFactory;
        parent::__construct($context, $data);
    }
    /*
     * get Product Collection by price class
     */
    public function getProductHtmlByPriceClass($price_class, $day){
        if ($this->_customerSession->isLoggedIn())
        {
            $menudataModel = $this->_modelMenudataFactory->create();
            $customerMenu = $menudataModel->getMenuDataByCustomerId($this->_customerSession->getCustomerId())->getData();
            switch ($day) {
                case 1:
                    $skus[] = explode(",",$customerMenu['product_mon']);
                    break;
                case 2:
                    $skus[] = explode(",",$customerMenu['product_tue']);
                    break;
                case 3:
                    $skus[] = explode(",",$customerMenu['product_wed']);
                    break;
                case 4:
                    $skus[] = explode(",",$customerMenu['product_thu']);
                    break;
                case 5:
                    $skus[] = explode(",",$customerMenu['product_fri']);
                    break;
            }
            error_log("skus für " . $day . ": " . json_encode($skus));
            $productCollection = $this->_productCollection->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', array('in' => $skus));
        } else
        {
            $productCollection = $this->_productCollection->addAttributeToFilter('price_class',$price_class);
        }
        $html_container ="";
        foreach ($productCollection as $product){
            $html_container .= $this->getProductCollectionHtml($product->getData('sku'));
        }
//        $this->_logger->addDebug(print_r($this->_productCollection->load()->getData(),true));
        return $html_container;
    }

    /*
     * generating html for product collection
     */
    public function getProductCollectionHtml($sku){
            $img_base_url = $this->getUrl('pub/media/catalog')."product";
            $product = $this->_productFactory->loadByAttribute('sku', $sku);
            $html = "<tr id='".$product->getSku()."'>
                        <td class='name'>
                            <img src='".$img_base_url.$product->getImage()."' scrset='".$img_base_url.$product->getImage()."' alt='".$product->getName()."' width='150px' height='150px' />
                            <h5>".$product->getName()."</h5>
                        </td>
                        <td class='description'><span>".$product->getShortDescription()."</span></td>
                        <td class='price'>
                            <div><span>".$product->getPrice()."&euro;</span></div>
                            <div><button class='choose_variant action primary' target_sku='".$product->getSku()."'>Auswählen</button></div>
                        </td>
                     </tr>";
        return $html;
    }
}