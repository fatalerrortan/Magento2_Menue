<?php
namespace Nextorder\Menue\Block\Frontend;

class Menue extends \Magento\Framework\View\Element\Template{

    protected $_logger;
    public $_helper;
    protected $_productCollection;
    protected $_customerSession;
//    public $_session_customer;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        \Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Catalog\Model\ProductFactory $productCollection, //product Factory injection
//        \Magento\Framework\Session\SessionManagerInterface $customerSession,
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_productCollection = $productCollection->create();
//        $this->_customerSession = $customerSession;
        $this->_session_customer = $this->getSession();
        parent::__construct($context, $data);
    }
    /*
     * load Predefined Products in Weekend Menu
     */
    public function loadProductHtmlBySku(){

        $sessionProducts = $this->_session_customer;
        if(empty($sessionProducts)){
            $products = $this->_helper->getAdminConfig();
        }else{
            foreach ($this->_helper->getAdminConfig() as $key => $value){
                if(empty($sessionProducts[$key])){
                    $sessionProducts[$key] = $value;
                }
            }
            $products = $sessionProducts;
        }

        $html = '';
        $index = 1;
        foreach ($products as $item) {
            $product = $this->_productCollection->loadByAttribute('sku', $item);
//            $this->_logger->addDebug($this->getUrl('pub/media/catalog').'product'.$product->getImage());
            $productName = $product->getName();
            $productPrice = $product->getPrice();
            $imgUrl = $this->getUrl('pub/media/catalog').'product'.$product->getImage();
            $productShortDescription = $product->getShortDescription();
            $priceClass = $product->getData('price_class');
            $html .= $this->getHtml($productName, $productPrice, $priceClass, $productShortDescription, $imgUrl, $item, $index);
            $index++;
        }
        return $html;
    }
    /*
     * load html wrapper for each product
     */
    public function getHtml($name, $price, $priceClass, $description, $imgUrl, $sku, $index){
        $this->getChildBlock("ListProduct")->setPriceClass($priceClass);
        $this->getChildBlock("ListProduct")->setMenuIndex($index);
        $html = "<tr sku='".$sku."' class='price_class_".$priceClass."' index=".$index.">
                <td class='img_container'>
                    <img src='".$imgUrl."' scrset='".$imgUrl."' alt='".$name."' width='200px' height='200px' />
                    <h5>".$name."</h5>
                </td> 
                <td class='product_info'>
                    <div class='product_content'>
                        <span>".$description."</span>
                    </div>
                    <div>
                        <button class='diy_button action primary' index='".$index."' price_class='".$priceClass."'>Austausch</button>
                        <div class='list_container'>
                        ".$this->getChildHtml('ListProduct',false)."
                        </div>
                    </div>
                 </td>
                 <td class='product_price'>
                    <span>".$price."&euro;</span>
                 </td>   
               </tr>";
        return $html;
    }
    /*
     * check status of session
     */
    public function getSession(){
        if(isset($_SESSION['user_choose'])){
            return array_filter(explode(",", $_SESSION['user_choose']));
        }
    }
    /*
     *  convert session params to json
     */
//    public function getSessionParam(){
//
//        foreach ($this->_session_customer as $session){
//
//        }
//    }
}