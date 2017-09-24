<?php
namespace Nextorder\Menue\Block\Frontend;

use Nextorder\MenuData\Model\MenudataFactory;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

class Menue extends \Magento\Framework\View\Element\Template{

    /**
     * @var \Nextorder\MenuData\Model\MenudataFactory
     */
    protected $_modelMenudataFactory;
    protected $_logger;
    public $_helper;
    protected $_productCollection;
    public $_customerSession;
    protected $_customerRepository;
    protected $_currentUserStatus;
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
                        $this->_currentUserStatus = "FIRST_LOGIN_WITH_WP";
                        $index++;
                    }
//                    $this->_logger->addDebug(print_r($products, true));
                }else{ //first login without connected firm
                    $products = $this->_helper->getAdminConfig();
                    $this->_currentUserStatus = "FIRST_LOGIN_WITHOUT_WP";
                }
            }else{
                $customerMenuSkus[] = explode(",",$customerMenu['product_mon']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_tue']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_wed']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_thu']);
                $customerMenuSkus[] = explode(",",$customerMenu['product_fri']);

//                $this->_logger->addDebug("Talent...............................................");
//                $this->_logger->addDebug(print_r($customerMenuSkus, true));

                if(!empty($authDataToWP)){ // login with connected firm
                    $this->_currentUserStatus = "LOGIN_WITH_WP";
                    $wpCosumerKey = $authDataToWP['wp_cosumer_key'];
                    $wpCosumerSecret = $authDataToWP['wp_cosumer_secret'];
                    $wpShopUrl = $authDataToWP['wp_shop_url'];
                    $remoteSkus = $this->getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret);

//                    $this->_logger->addDebug("Remote SKUs...............................................");
//                    $this->_logger->addDebug(print_r($remoteSkus, true));

                    for ($i = 0; $i<5 ; $i++) {
//                        $this->_logger->addDebug("SKU from OptionIDs...............................................");
//                        $optionIds = array_keys($bundles);
//                        $this->_logger->addDebug(print_r(array_keys($bundles[$optionIds[$i]]), true));
                        $toAssignSku = null;
                        $optionIds = array_keys($bundles);
                        foreach ($customerMenuSkus[$i] as $currentMenuDataSku){
                            if(in_array($currentMenuDataSku, $remoteSkus)){
                                $toAssignSku = $currentMenuDataSku;
                                break;
                            }
                        }
                        if(empty($toAssignSku)){
                            if(in_array($localDefaultSKus[$i], $remoteSkus)){
                                $products[] = $localDefaultSKus[$i];
                            }else{
                                $skuToAssign = array_intersect($remoteSkus, array_keys($bundles[$optionIds[$i]]));
                                if(!empty($skuToAssign)){
                                    $products[] = current($skuToAssign);
                                }else{
                                    $products[] = 'NO_MATCH';

                                }
                            }
                        }else{
                            $products[] = $toAssignSku;
                        }
                    }
//                    $this->_logger->addDebug("To Cart...............................................");
//                    $this->_logger->addDebug(print_r($products, true));
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
// ------------------------------------- new attempt end-------------------------------------------------------
                }else{ // login without connected firm
                    $this->_currentUserStatus = "LOGIN_WITHOUT_WP";
                    for ($i = 0; $i<5 ; $i++) {
                        $products[] = $customerMenuSkus[$i][0];
                    }
                }
            }
        }else{ // Not login => free user
            $products = $this->_helper->getAdminConfig();
            $this->_currentUserStatus = "NO_LOGIN";
        }
        $html = '';
        $index = 1;
        $optionIds = array_keys($bundles);
        $optionIdIndex = 0;
//            $this->_logger->addDebug(print_r($products, true));
        foreach ($products as $item) {
            switch ($this->_currentUserStatus){
                case "NO_LOGIN":
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "NO_LOGIN"
                    ));
                    break;
                case "FIRST_LOGIN_WITHOUT_WP":
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "FIRST_LOGIN_WITHOUT_WP"
                    ));
                    break;
                case "FIRST_LOGIN_WITH_WP":
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "FIRST_LOGIN_WITH_WP",
                        "remote_skus" => $remoteSkus,
                        'option_skus' => array_keys($bundles[$optionIds[$optionIdIndex]])
                    ));
                    break;
                case "LOGIN_WITH_WP":
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "LOGIN_WITH_WP",
                        "remote_skus" => $remoteSkus,
                        "talent_skus" => $customerMenuSkus[$optionIdIndex],
                        'option_skus' => array_keys($bundles[$optionIds[$optionIdIndex]])
                    ));
                    break;
                case "LOGIN_WITHOUT_WP":
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "LOGIN_WITHOUT_WP",
                        "talent_skus" => $customerMenuSkus[$optionIdIndex]
                    ));
                    break;
                default:
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "NO_LOGIN"
                    ));
                    break;
            }
            if($item === "NO_MATCH") {
                $html .= $this->getHtml('Sorry', 0, '', '<b>Kein Gericht ist für den Tag verfügbar</b>', '', 'disable', $index, $optionIds[$optionIdIndex]);
            }else{
                $product = $this->_productCollection->loadByAttribute('sku', $item);
                $productName = $product->getName();
                $productPrice = $product->getPrice();
                $imgUrl = $this->getUrl('pub/media/catalog').'product'.$product->getImage();
                $productShortDescription = $product->getShortDescription();
                $priceClass = $product->getData('price_class');
                $html .= $this->getHtml($productName, $productPrice, $priceClass, $productShortDescription, $imgUrl, $item, $index, $optionIds[$optionIdIndex]);
            }
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
     */
    public function getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret){
//        $this->_logger->addDebug(print_r($wpShopUrl, true));
//        $this->_logger->addDebug(print_r($wpCosumerKey, true));
//        $this->_logger->addDebug(print_r($wpCosumerSecret, true));
        $woocommerce = new Client(
            $wpShopUrl,
            $wpCosumerKey,
            $wpCosumerSecret,
            [
//                'wp_api' => true,
//                'version' => 'wc/v1'
            ]
        );
        $remoteSkus = array();
        try{
            $remoteProducts = $woocommerce->get('products');
        }catch (HttpClientException $e){
            $this->_logger->addDebug(print_r($e->getRequest(), true));
            $this->_logger->addDebug(print_r($e->getResponse(), true));
        }
//        $this->_logger->addDebug(print_r($remoteProducts, true));
        foreach ($remoteProducts['products']as $product){
//            $this->_logger->addDebug(print_r($product, true));
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

    public function getOrderDate($nextWeek = true){
        $dayOfWeekId = date('w');
        $spreadToNextWeek = 5 - $dayOfWeekId;
        if($nextWeek){
            // for next week
            $beginWeek = time() + (($spreadToNextWeek + 3) * 24 * 60 * 60);
            $endWeek = $beginWeek + (4 * 24 * 60 * 60);
        }else{
            // for this week
            $beginWeek = time() - (($dayOfWeekId - 1) * 24 * 60 * 60);
            $endWeek = $beginWeek + (4 * 24 * 60 * 60);
        }
        return array(
            'begin' => date('Y-m-d', $beginWeek),
            'end' => date('Y-m-d', $endWeek),
            'raw_begin' => $beginWeek,
            'raw_end' => $endWeek
        );
    }
}