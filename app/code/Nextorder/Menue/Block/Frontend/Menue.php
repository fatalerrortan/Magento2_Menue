<?php
namespace Nextorder\Menue\Block\Frontend;

class Menue extends \Magento\Framework\View\Element\Template{

    protected $_logger;
    public $_helper;
    protected $_productCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        \Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Catalog\Model\ProductFactory $productCollection, //product collection injection
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_productCollection = $productCollection->create();
        parent::__construct($context, $data);
    }
    /*
     * load Predefined Products in Weekend Menu
     */
    public function loadProductHtmlBySku(){

        $products = $this->_helper->getAdminConfig();
        $html = '';
        foreach ($products as $item) {
            $product = $this->_productCollection->loadByAttribute('sku', $item);
//            $this->_logger->addDebug($this->getUrl('pub/media/catalog').'product'.$product->getImage());
            $productName = $product->getName();
            $productPrice = $product->getPrice();
            $imgUrl = $this->getUrl('pub/media/catalog').'product'.$product->getImage();
            $productShortDescription = $product->getShortDescription();
            $priceClass = $product->getData('price_class');
            $html .= $this->getHtml($productName, $productPrice, $priceClass, $productShortDescription, $imgUrl);
        }
        return $html;
    }
    /*
     * load html wrapper for each product
     */
    public function getHtml($name, $price, $priceClass, $description, $imgUrl){

        $html = "<tr>
                <td class='img_container'>
                    <img src='".$imgUrl."' scrset='".$imgUrl."' alt='".$name."' width='200px' height='200px' />
                    <h5>".$name."</h5>
                </td> 
                <td class='product_info'>
                    <div class='product_content'>
                        <span>".$description."</span>
                    </div>
                    <div class='diy_button'>
                        <button price_class='".$priceClass."'>Austausch</button>
                    </div>
                 </td>
                 <td class='product_price'>
                    <span>".$price."&euro;</span>
                 </td>   
               </tr>";
        return $html;
    }
}