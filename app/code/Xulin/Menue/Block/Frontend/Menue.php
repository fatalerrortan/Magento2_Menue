<?php
namespace Nextorder\Menue\Block\Frontend;

use Nextorder\MenuData\Model\MenudataFactory;

class Menue extends \Magento\Framework\View\Element\Template{

    /**
     * @var \Nextorder\MenuData\Model\MenudataFactory
     */
    protected $_modelMenudataFactory;

    //protected $_logger;
    public $_helper;
    protected $_productCollection;
    protected $_customerSession;
//    public $_session_customer;

    /**
     * @param Context $context
     * @param MenudataFactory $modelMenudataFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        //\Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Catalog\Model\ProductFactory $productCollection, //product Factory injection
//        \Magento\Framework\Session\SessionManagerInterface $customerSession,
        \Magento\Customer\Model\Session $customerSession,
        MenudataFactory $modelMenudataFactory,
        array $data = []
    )
    {
        $this->_helper = $helper;
        //$this->_logger = $logger;
        $this->_productCollection = $productCollection->create();
        $this->_customerSession = $customerSession;
        $this->_session_customer = $this->getSession();
        $this->_modelMenudataFactory = $modelMenudataFactory;
        parent::__construct($context, $data);
    }
    /*
     * load Predefined Products in Weekend Menu
     */
    public function loadProductHtmlBySku(){

        $menudataModel = $this->_modelMenudataFactory->create();
        $customerMenu = null;
        $customerMenuSkus = array();
        if ($this->_customerSession->isLoggedIn())
        {
            $customerMenu = $menudataModel->getMenuDataByCustomerId($this->_customerSession->getCustomerId())->getData();
            $customerMenuSkus[] = explode(",",$customerMenu['product_mon']);
            $customerMenuSkus[] = explode(",",$customerMenu['product_tue']);
            $customerMenuSkus[] = explode(",",$customerMenu['product_wed']);
            $customerMenuSkus[] = explode(",",$customerMenu['product_thu']);
            $customerMenuSkus[] = explode(",",$customerMenu['product_fri']);
        }
        $sessionProducts = $this->_session_customer;
        if(empty($sessionProducts)){
            $products = $this->_helper->getAdminConfig();
            if ($this->_customerSession->isLoggedIn())
            {
                $products = array();
                for ($i = 0; $i<5 ; $i++)
                {

                    $products[] = $customerMenuSkus[$i][0];
                }
            }
            /* beim ersten Login ist es noch leer */
            if (empty($products)) {
                $products = $this->_helper->getAdminConfig();
            }
        }else{
            foreach ($this->_helper->getAdminConfig() as $key => $value){
                if(empty($sessionProducts[$key])){
                    $sessionProducts[$key] = $value;
                }
            }
            $products = $sessionProducts;
            if ($this->_customerSession->isLoggedIn())
            {
                $products = array();
                for ($i = 0; $i<5 ; $i++)
                {

                    $products[] = $customerMenuSkus[$i][0];
                }
            }
            if (empty($products)) {
                $products = $sessionProducts;
            }
        }

        $html = '';
        $index = 1;

        $bundles = $this->_helper->getSerializedData('inc','bundleDataSource.txt');
        $optionIds = array_keys($bundles);
        $optionIdIndex = 0;

        foreach ($products as $item) {
            $product = $this->_productCollection->loadByAttribute('sku', $item);
//            $this->_logger->addDebug($this->getUrl('pub/media/catalog').'product'.$product->getImage());
            $productName = $product->getName();
            $productPrice = $product->getPrice();
            $imgUrl = $this->getUrl('pub/media/catalog').'product'.$product->getImage();
            $productShortDescription = $product->getShortDescription();
            $priceClass = $product->getData('price_class');
            $html .= $this->getHtml($productName, $productPrice, $priceClass, $productShortDescription, $imgUrl, $item, $index, $optionIds[$optionIdIndex]);
            $index++;
            $optionIdIndex++;
        }
        return $html;
    }
    /*
     * load html wrapper for each product
     */
    public function getHtml($name, $price, $priceClass, $description, $imgUrl, $sku, $index, $optionId){
        $week = array(
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoche',
            4 => 'Donnerstag',
            5 => 'Freitag'
        );
        $this->getChildBlock("ListProduct")->setPriceClass($priceClass);
        $this->getChildBlock("ListProduct")->setMenuIndex($index);
        $this->getChildBlock("ListProduct")->setOptionIdindex($optionId);
        $html = "<tr sku='".$sku."' class='price_class_".$priceClass."' index=".$index.">
                <td class='menue_tag'>
                    <b>".$week[$index]."</b>
                </td>
                <td class='img_container'>
                    <img src='".$imgUrl."' scrset='".$imgUrl."' alt='".$name."' width='200px' height='200px' />
                    <h5>".$name."</h5>
                </td> 
                <td class='product_info'>
                    <div class='product_content'>
                        <span>".$description."</span>
                    </div>
                    <div>
                        <button class='diy_button action primary' index='".$index."' price_class='".$priceClass."' day='".$index."'>Austausch</button>
                        <div class='list_container'>
                        ".$this->getChildHtml('ListProduct',false)."
                        </div>
                    </div>
                 </td>
                 <td class='product_price'>
                    <span>".$price."&euro;</span>
                 </td>  
                 <td class='status'>
                    <button class='status_button active' onclick='menuStatus(this)'>Disable</button>
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