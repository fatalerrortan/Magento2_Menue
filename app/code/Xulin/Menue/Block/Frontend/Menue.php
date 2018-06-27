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
    public $_inStockSkus;
    public $_isloggedIn = false;
    protected $_remoteSkus;
    protected $_remoteSideSkus;
//    public $_session_customer;

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
    /**
     * @param bool $isSideMenu
     * @param bool $isAppetizer
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadProductHtmlBySku($isSideMenu = false, $isAppetizer = false){
        $menudataModel = $this->_modelMenudataFactory->create();
        $customerMenu = null;
        $customerMenuSkus = array();
        $products = array();
        // @array pre-defined dafault products in admin config
        $menuType = $isSideMenu ? 'side' : 'main';
        $localDefaultSKus = $this->_helper->getAdminConfig()[$menuType];
        // @array all products assigned in the bundle product container
        $bundles = $this->_helper->getSerializedData('inc','bundleDataSource.txt');
        if ($this->_customerSession->isLoggedIn()) {
            $this->_isloggedIn = true;
            $customerMenu = $menudataModel->getMenuDataByCustomerId($this->_customerSession->getCustomerId())->getData();
            $authDataToWP = $this->userValidate();
            if(empty($customerMenu)){//first login
                if(!empty($authDataToWP)){ //first login with connected firm
                    $wpCosumerKey = $authDataToWP['wp_cosumer_key'];
                    $wpCosumerSecret = $authDataToWP['wp_cosumer_secret'];
                    $wpShopUrl = $authDataToWP['wp_shop_url'];
                    $remoteSkus = $isSideMenu ?
                        $this->_remoteSideSkus:
                        $this->getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret);
                    if($isSideMenu && $isAppetizer){
                        $index =  5;
                    }elseif ($isSideMenu && !$isAppetizer){
                        $index =  10;
                    }else{
                        $index =  0;
                    }
                    foreach ($localDefaultSKus as $localDefaultSKu){
                        if(in_array($localDefaultSKu, $remoteSkus['in_stock'])){
                            $products[] = $localDefaultSKu;
                        }else{
                            $optionIds = array_keys($bundles);
                            $skuToAssign = null;
//                            $this->_logger->addDebug(print_r(array_keys($bundles[$optionIds[$index]]), true));
                            foreach($remoteSkus['in_stock'] as $remoteSku){
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
                }else{ //first login without connected firm
                    $products = $isSideMenu ?
                        $this->_helper->getAdminConfig()['side']:
                        $this->_helper->getAdminConfig()['main'];
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
                    $remoteSkus = $isSideMenu ?
                        $this->_remoteSideSkus:
                        $this->getRemoteSkus($wpShopUrl, $wpCosumerKey, $wpCosumerSecret);

//                    $this->_logger->addDebug("Remote SKUs...............................................");
                    for ($i = 0; $i<5 ; $i++) {
//                        $this->_logger->addDebug("SKU from OptionIDs...............................................");
//                        $optionIds = array_keys($bundles);
//                        $this->_logger->addDebug(print_r(array_keys($bundles[$optionIds[$i]]), true));
                        $toAssignSku = null;
                        $optionIds = array_keys($bundles);
                        foreach ($customerMenuSkus[$i] as $currentMenuDataSku){
                            if(in_array($currentMenuDataSku, $remoteSkus['in_stock'])){
                                $toAssignSku = $currentMenuDataSku;
                                break;
                            }
                        }
                        if(empty($toAssignSku)){
                            if(in_array($localDefaultSKus[$i], $remoteSkus['in_stock'])){
                                $products[] = $localDefaultSKus[$i];
                            }else{
                                if($isSideMenu && $isAppetizer){
                                    $sideMenuIndex =  5;
                                }elseif ($isSideMenu && !$isAppetizer){
                                    $sideMenuIndex =  10;
                                }else{
                                    $sideMenuIndex =  0;
                                }
                                $skuToAssign = array_intersect($remoteSkus['in_stock'], array_keys($bundles[$optionIds[$i+$sideMenuIndex]]));
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
                }else{ // login without connected firm
                    $this->_currentUserStatus = "LOGIN_WITHOUT_WP";
                    if($isSideMenu){
                        $products = $this->_helper->getAdminConfig()['side'];
                    }else{
                        for ($i = 0; $i<5 ; $i++) {
                            $products[] = $customerMenuSkus[$i][0];
                        }
                    }
                }
            }
        }else{ // Not login => free user
            if($isSideMenu){
                return "<center><h3>
                    Bestellen Sie das Wochenmenü mit Ihrem personalisierten Ernährungsziel nach 
                    <a href='".$this->getUrl('customer/account/login')."'>Einloggen</a>
                    </h3></center>";
            }else{
                $products = $this->_helper->getAdminConfig()['main'];
            }
            $this->_currentUserStatus = "NO_LOGIN";
        }
        $html = '';
        $optionIds = array_keys($bundles);
        if($isSideMenu && $isAppetizer){
            $index =  6;
        }elseif ($isSideMenu && !$isAppetizer){
            $index =  11;
        }else{
            $index =  1;
        }
        $menuDataIndex = 0;
        if($isSideMenu && $isAppetizer){
            $optionIdIndex =  5;
        }elseif ($isSideMenu && !$isAppetizer){
            $optionIdIndex =  10;
        }else{
            $optionIdIndex =  0;
        }
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
                        "talent_skus" => $customerMenuSkus[$menuDataIndex],
                        'option_skus' => array_keys($bundles[$optionIds[$optionIdIndex]])
                    ));
                    break;
                case "LOGIN_WITHOUT_WP":
                    $this->getChildBlock("ListProduct")->setCurrentUserStatus(array(
                        "type" => "LOGIN_WITHOUT_WP",
                        "talent_skus" => $customerMenuSkus[$menuDataIndex]
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
                $html .= $this->getHtml($productName, $productPrice, $priceClass, $productShortDescription, $imgUrl, $item, $index, $optionIds[$optionIdIndex],'','',$isSideMenu);
            }
            $index++;
            $menuDataIndex++;
            $optionIdIndex++;
        }
        return $html;
    }
    /**
     * validate whether the user is bound to a company
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
            $remoteProducts = $woocommerce->get('products', ['status' => 'publish']);
        }catch (HttpClientException $e){
            $this->_logger->addDebug(print_r($e->getRequest(), true));
            $this->_logger->addDebug(print_r($e->getResponse(), true));
        }
        foreach ($remoteProducts['products']as $product){
            if(empty($product['sku'])){continue;}

            $keyAttrMainDish = array_search('is_main_dish', array_column($product['attributes'], 'slug'));
            $isMainDish = reset($product['attributes'][$keyAttrMainDish]['options']);

            if($isMainDish != 'true'){
//                $this->_logger->addDebug("is Main DIsh");
                $this->_remoteSideSkus['all'][] = $product['sku'];
                if($product['in_stock']){
                    $this->_remoteSideSkus['in_stock'][] = $product['sku'];
                }
            }
            $remoteSkus['all'][] = $product['sku'];
            if($product['in_stock']){
                $remoteSkus['in_stock'][] = $product['sku'];
            }
        }
        $this->_inStockSkus = implode(",",$remoteSkus['in_stock']);
        $this->_remoteSkus = $remoteSkus;
//        $this->_logger->addDebug(print_r($this->_remoteSideSkus, true));
        return $remoteSkus;
    }
    /**
     * @param string $name
     * @param $price
     * @param $priceClass
     * @param $description
     * @param $imgUrl
     * @param $sku
     * @param $index
     * @param $optionId
     * @param string $buttonStatus
     * @param string $disableStyle
     * @return string
     */
    public function getHtml($name, $price, $priceClass, $description,
                            $imgUrl, $sku, $index, $optionId, $buttonStatus = '', $disableStyle = '', $isSidemenu){
        $week = array(
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoche',
            4 => 'Donnerstag',
            5 => 'Freitag',
            6 => 'Vorspeise für Montag',
            7 => 'Vorspeise für Dienstag',
            8 => 'Vorspeise für Mittwoche',
            9 => 'Vorspeise für Donnerstag',
            10 => 'Vorspeise für Freitag',
            11 => 'Nachspeise für Montag',
            12 => 'Nachspeise für Dienstag',
            13 => 'Nachspeise für Mittwoche',
            14 => 'Nachspeise für Donnerstag',
            15 => 'Nachspeise für Freitag'
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
                        <button class='diy_button action primary' onclick='menuChange(this)' index='".$index."' price_class='".$priceClass."' day='".$index."' ".$buttonStatus.">Austausch</button>
                        <div class='reload_comment' style='color: #2b542c'></div>
                        <div class='list_container'>
                        ".$this->getChildHtml('ListProduct',false)."
                        </div>
                    </div>
                 </td>
                 <td class='product_price'>
                    <span>".$price."&euro;</span>
                 </td>  
                 <td class='status'>
                    <button class='status_button active' onclick='menuStatus(this, ".$isSidemenu.")' ".$buttonStatus.">Disable</button>
                 </td>
               </tr>";
        return $html;
    }

    /**
     * @return array
     */
    public function getSession(){
        if(isset($_SESSION['user_choose'])){
            return array_filter(explode(",", $_SESSION['user_choose']));
        }
    }
    /**
     * @param bool $nextWeek
     * @return array
     */
    public function getOrderDate($nextWeek = true){
        $dayOfWeekId = date('w');
        $this->_logger->addDebug(print_r("get Order Date", true));
        $this->_logger->addDebug(print_r($dayOfWeekId, true));
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
    /**
     * @return array
     */
    public function getNutritionGoalLabel(){
        if ($this->_customerSession->isLoggedIn()) {
            $attrCode = $this->_customerSession->getCustomer()->getData('nof_goal');
            if(empty($attrCode)){
                return [false,"Wollen Sie ein Ernährungsziel für Ihr Mittagessen nehmen? <a href='".$this->getUrl('customer/account/edit')."' target='_blank'>Einfach hier Klick</a>"];
            }
            $goalLabel = $this->_helper->getCustomerAttrLabel('nof_goal', true, $attrCode);
            $hint = $this->_helper->getNutritionGoalWithString(strtolower($goalLabel))['hint'];
            return [
                true,
                "<ul><li>Ihr aktuell Ernährungsziel： <b style='color: green'>".$goalLabel."</b>; Wollen Sie ein anderes Ernährungsziel probieren? <a href='".$this->getUrl('customer/account/edit')."'>Einfach hier Klicken</a>
                </li><li>".$hint." <a href='".$this->getUrl('customer/account/edit')."' target='_blank'>Bitte stellen Sie sicher hier</a></li></ul>"
            ];
        }
    }
    /**
     * get weight of current user
     * @return mixed
     */
    public function getFormatedWeight(){
        return $this->_customerSession->getCustomer()->getData('body_weight');
    }
}