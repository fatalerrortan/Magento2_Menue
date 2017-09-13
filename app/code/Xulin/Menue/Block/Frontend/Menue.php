<?php
namespace Nextorder\Menue\Block\Frontend;

use Nextorder\MenuData\Model\MenudataFactory;
use Automattic\WooCommerce\Client;

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
        $localDefaultSKus = $this->_helper->getAdminConfig();
        $bundles = $this->_helper->getSerializedData('inc','bundleDataSource.txt');
        if ($this->_customerSession->isLoggedIn()) {
            $customerMenu = $menudataModel->getMenuDataByCustomerId($this->_customerSession->getCustomerId())->getData();
            $authDataToWP = $this->userValidate();
            if(empty($customerMenu)){//first login
                if(!empty($authDataToWP)){ //first login with connected firm
                    $wpCosumerKey = $authDataToWP['wp_cosumer_key'];
                    $wpCosumerSecret = $authDataToWP['wp_cosumer_secret'];
                    $wpShopUrl = $authDataToWP['wp_shop_url'];
                    $remoteSkus = $this->getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret);
//                    $this->_logger->addDebug(print_r($remoteSkus, true));
                    $index = 0;
                    foreach ($localDefaultSKus as $localDefaultSKu){
                        if(in_array($localDefaultSKu, $remoteSkus)){
                            $products[] = $localDefaultSKu;
                        }else{
                            $optionIds = array_keys($bundles);
                            $skuToAssign = null;
//                            $this->_logger->addDebug(print_r(array_keys($bundles[$optionIds[$index]]), true));
                            foreach($remoteSkus as $remoteSku){
                                if(in_array($remoteSku, array_keys($bundles[$optionIds[$index]]))){
                                    $skuToAssign = $remoteSku;
                                    break;
                                }
                            }
                            if(!empty($skuToAssign)){
                                $products[] = $skuToAssign;
                            }else{
                                $products[] = 'NO_MATCH';
                            }
                        }
                        $index++;
                    }
//                    $this->_logger->addDebug(print_r($products, true));
                }else{ //first login without connected firm
                    $products = $this->_helper->getAdminConfig();
                }
            }else{
                $customerMenuSkus[] = explode(",",$customerMenu['product_mon']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_tue']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_wed']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_thu']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_fri']);

                if(!empty($authDataToWP)){ // login with connected firm
                    $wpCosumerKey = $authDataToWP['wp_cosumer_key'];
                    $wpCosumerSecret = $authDataToWP['wp_cosumer_secret'];
                    $wpShopUrl = $authDataToWP['wp_shop_url'];
                    $remoteSkus = $this->getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret);
                    for ($i = 0; $i<5 ; $i++) {
                        $toAssignSku = null;
                        foreach ($customerMenuSkus[$i] as $currentMenuDataSku){
                            if(in_array($currentMenuDataSku, $remoteSkus)){
                                $toAssignSku = $currentMenuDataSku;
                                break;
                            }
                        }
//                        $this->_logger->addDebug("Assigned to Index: ".$i);
//                        $this->_logger->addDebug(print_r($toAssignSku, true));
                        if(empty($toAssignSku)){
                            $products[] = $this->$remoteSkus[$i];
                        }else{
                            $products[] = $toAssignSku;
                        }
                    }

// ------------------------------------- new attempt start--------------------------------------------------------
//                    $weekToList = array();
//                    for ($i = 0; $i<5 ; $i++) {
//                        $toDefaultSku = null;
//                        $dayToList = array();
//                        foreach ($customerMenuSkus[$i] as $currentMenuDataSku){
//                            if(in_array($currentMenuDataSku, $remoteSkus)){
//                                if(empty($toDefaultSku)){
//                                    $toDefaultSku = $currentMenuDataSku;
//                                }
//                                $dayToList[] = $currentMenuDataSku;
//                            }
//                        }
//                        $weekToList[] = $dayToList;
//                        if(empty($toDefaultSku)){
//                            $products[] = $this->_helper->getAdminConfig()[$i];
//                        }else{
//                            $products[] = $toDefaultSku;
//                        }
//                    }
// ------------------------------------- new attempt end--------------------------------------------------------
                }else{ // login without connected firm
                    for ($i = 0; $i<5 ; $i++) {
                        $products[] = $customerMenuSkus[$i][0];
                    }
                }
            }
        }else{ // Not login => free user
            $products = $this->_helper->getAdminConfig();
        }
        $html = '';
        $index = 1;
        $optionIds = array_keys($bundles);
        $optionIdIndex = 0;
//            $this->_logger->addDebug(print_r($products, true));
        foreach ($products as $item) {
            if($item === "NO_MATCH"){
                $html .= $this->getHtml('Sorry', 0, '', '<b>Kein Gericht ist für den Tag verfügbar</b>', '', 'disable', $index, $optionIds[$optionIdIndex]);
                $index++;
                $optionIdIndex++;
                continue;
            }
            $product = $this->_productCollection->loadByAttribute('sku', $item);
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
     */
    public function userValidate(){
        $currentCustomer = $this->_customerSession;
//        $this->_logger->addDebug(print_r($currentCustomer->getCustomer()->getData(), true));
        $parentEmail = $currentCustomer->getCustomer()->getData('parent_email');
        $wpCosumerKey = $currentCustomer->getCustomer()->getData('wp_cosumer_key');
        $wpCosumerSecret = $currentCustomer->getCustomer()->getData('wp_cosumer_secret');
        $wpShopUrl = $currentCustomer->getCustomer()->getData('wp_shop_url');
        if( (!empty($parentEmail))
            ||
            ((!empty($wpCosumerKey)) && (!empty($wpCosumerSecret)))
        ) {
            if(empty($parentEmail)){ // current account should be super account.
                return array(
                    'wp_cosumer_key' => $wpCosumerKey,
                    'wp_cosumer_secret' => $wpCosumerSecret,
                    'wp_shop_url' => $wpShopUrl
                );
            }else{
                // condition => parent ID exists => load parent obj to get key and secret
                $parent = $this->_customerRepository->get($parentEmail);
                return array(
                    'wp_cosumer_key' => $parent->getCustomAttribute('wp_cosumer_key')->getValue(),
                    'wp_cosumer_secret' => $parent->getCustomAttribute('wp_cosumer_secret')->getValue(),
                    'wp_shop_url' => $parent->getCustomAttribute('wp_shop_url')->getValue(),
                );
            }
        }
        else{
            return array();
        }
    }
    /*
     * load in stock skus from remote wordpress
     * @ return array
     */
    public function getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret){
        $woocommerce = new Client(
            $wpShopUrl,
            $wpCosumerKey,
            $wpCosumerSecret,
            [
                'wp_api' => true,
                'version' => 'wc/v1'
            ]
        );
        $remoteSkus = array();
        $remoteProducts = $woocommerce->get('products');
        foreach ($remoteProducts as $product){
            if($product['in_stock']){
                $remoteSkus[] = $product['sku'];
            }
        }
        return $remoteSkus;
    }
    /*
     * load html wrapper for each product
     */
    public function getHtml($name, $price, $priceClass, $description,
                            $imgUrl, $sku, $index, $optionId, $buttonStatus = '', $disableStyle = ''){
        $week = array(
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoche',
            4 => 'Donnerstag',
            5 => 'Freitag'
        );
        if($sku === 'disable'){$buttonStatus = 'disabled'; $disableStyle = 'background-color: grey';}
        $this->getChildBlock("ListProduct")->setPriceClass($priceClass);
        $this->getChildBlock("ListProduct")->setMenuIndex($index);
        $this->getChildBlock("ListProduct")->setOptionIdindex($optionId);
        $this->_logger->addDebug(print_r($sku, true));
        $html = "<tr sku='".$sku."' class='price_class_".$priceClass."' index=".$index." style='".$disableStyle."'>
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
                        <button class='diy_button action primary' index='".$index."' price_class='".$priceClass."' day='".$index."' ".$buttonStatus.">Austausch</button>
                        <div class='list_container'>
                        ".$this->getChildHtml('ListProduct',false)."
                        </div>
                    </div>
                 </td>
                 <td class='product_price'>
                    <span>".$price."&euro;</span>
                 </td>  
                 <td class='status'>
                    <button class='status_button active' onclick='menuStatus(this)' ".$buttonStatus.">Disable</button>
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