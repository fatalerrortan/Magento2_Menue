<?php
namespace Nextorder\Menue\Block\Frontend;

use Nextorder\MenuData\Model\MenudataFactory;

class Menue extends \Magento\Framework\View\Element\Template{

    /**
     * @var \Nextorder\MenuData\Model\MenudataFactory
     */
    protected $_modelMenudataFactory;

    protected $_logger;
    public $_helper;
    protected $_productCollection;
    protected $_customerSession;
    protected $_customerRepository;
//    public $_session_customer;

    /**
     * @param Context $context
     * @param MenudataFactory $modelMenudataFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        \Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Catalog\Model\ProductFactory $productCollection, //product Factory injection
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
//        \Magento\Framework\Session\SessionManagerInterface $customerSession,
        \Magento\Customer\Model\Session $customerSession,
        MenudataFactory $modelMenudataFactory,
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_customerRepository = $customerRepository;
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
        $products = array();
        if ($this->_customerSession->isLoggedIn()) {
            $customerMenu = $menudataModel->getMenuDataByCustomerId($this->_customerSession->getCustomerId())->getData();
            if(empty($customerMenu)){
                // no bound data to talent => e.g. first login
                $products = $this->_helper->getAdminConfig();
            }else{
                $customerMenuSkus[] = explode(",",$customerMenu['product_mon']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_tue']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_wed']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_thu']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_fri']);

                $authDataToWP = $this->userValidate();
                if(!empty($authDataToWP)){
//                    $this->_logger->addDebug(print_r($this->userValidate(), true));
                    $wpCosumerKey = $authDataToWP['wp_cosumer_key'];
                    $wpCosumerSecret = $authDataToWP['wp_cosumer_secret'];
                }else{
                    //accouts which has data from talent, but not connected to WP (Free Registed User)
                    $this->_logger->addDebug(print_r('NO Connected', true));
                }

                for ($i = 0; $i<5 ; $i++) {
                    $products[] = $customerMenuSkus[$i][0];
                }
            }
        }else{
            $products = $this->_helper->getAdminConfig();
        }
        $html = '';
        $index = 1;

        $bundles = $this->_helper->getSerializedData('inc','bundleDataSource.txt');
        $optionIds = array_keys($bundles);
        $optionIdIndex = 0;
//            $this->_logger->addDebug(print_r($products, true));
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
     * validate whether the user is bound to a company
     * @ return boolean
     */
    public function userValidate(){
        $currentCustomer = $this->_customerSession;
//        $this->_logger->addDebug(print_r($currentCustomer->getCustomer()->getData(), true));
        $parentEmail = $currentCustomer->getCustomer()->getData('parent_email');
        $wpCosumerKey = $currentCustomer->getCustomer()->getData('wp_cosumer_key');
        $wpCosumerSecret = $currentCustomer->getCustomer()->getData('wp_cosumer_secret');
        if( (!empty($parentEmail))
            ||
            ((!empty($wpCosumerKey)) && (!empty($wpCosumerSecret)))
        ) {
            if(empty($parentEmail)){ // current account should be super account.
                $this->_logger->addDebug(print_r('use data directly', true));
                return array(
                    'wp_cosumer_key' => $wpCosumerKey,
                    'wp_cosumer_secret' => $wpCosumerSecret
                );
            }else{
                // condition => parent ID exists => load parent obj to get key and secret
                $this->_logger->addDebug(print_r('load parent', true));
                $parent = $this->_customerRepository->get($parentEmail);
                return array(
                    'wp_cosumer_key' => $parent->getCustomAttribute('wp_cosumer_key')->getValue(),
                    'wp_cosumer_secret' => $parent->getCustomAttribute('wp_cosumer_secret')->getValue()
                );
            }
        }
        else{
            return array();
        }
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