<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Cart extends \Magento\Framework\App\Action\Action{

    protected $_cart;
    protected $_productRepository;
//    protected $_resultJsonFactory;
    protected $_idsAndOptionIds;
    protected $_checkoutSession;

    public function __construct(
                                Context $context,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Checkout\Model\Session $checkoutSession,
//                                \Magento\Checkout\Model\Session\Interceptor $interceptor,
                                \Magento\Catalog\Model\ProductRepository $productRepository
                        ){
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
        $this->_idsAndOptionIds = $this->getIdAndOptionId('inc','optionIds.txt');
        parent::__construct($context);
    }
    public function execute(){

        if ($this->getRequest()->getParam("menu_orders")){
            $menu_orders_skus = explode(",", $this->getRequest()->getParam("menu_orders"));
            if($this->addProductsInCart($menu_orders_skus)){echo "worked";}
        }
//        $resultRedirect = $this->resultRedirectFactory->create();
//        return $resultRedirect->setPath('checkout/cart/index');
    }
/*
 *  generate Orders in Cart
 * @var orders array
 */
    protected function addProductsInCart($skus){
        $orderedChildrenProducts = array();
        foreach ($skus as $sku){
            if(empty($sku)){continue;}
            $orderedChildrenProducts[$this->_idsAndOptionIds['__main__'][0]][$this->_idsAndOptionIds['__children__'][$sku]['product_id']] = $this->_idsAndOptionIds['__children__'][$sku]["option_id"];
        }
        $params = [
            'uenc' => null,
            'product' => $this->_idsAndOptionIds['__children__']['test_bundle']['product_id'],
            'selected_configurable_option' => null,
            'related_product' => null,
            'form_key' => null,
            'bundle_option' => $orderedChildrenProducts,
            'qty' => 1
        ];
        if (isset($params['qty'])) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
            );
        }
            $params['qty'] = $filter->filter($params['qty']);
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $product = $this->_productRepository->getById($this->_idsAndOptionIds['__children__']['test_bundle']['product_id'], false, $storeId);
        $this->_cart->addProduct($product,$params);
        $this->_cart->save();
        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
        );
        if (!$this->_checkoutSession->getNoCartRedirect(true)) {
            if (!$this->_cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $product->getName()
                );
                $this->messageManager->addSuccessMessage($message);
            }
        }
        return true;
    }
    /*
     * get related Id and Option id according to sku
     */
    public function getIdAndOptionId($dir, $file){
        $serializedArray = file_get_contents($this->df_module_dir("Nextorder_Menue")."/".$dir."/".$file);
        return unserialize($serializedArray);
    }
    /*
    * get module dir to save serialized array of option ids
    */
    public  function df_module_dir($moduleName, $type = '') {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $reader = $om->get('Magento\Framework\Module\Dir\Reader');
        return $reader->getModuleDir($type, $moduleName);
    }
}