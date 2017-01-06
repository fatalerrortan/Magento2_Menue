<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Cart extends \Magento\Framework\App\Action\Action{

    protected $_cart;
    protected $_productRepository;
//    protected $_resultJsonFactory;
    protected $_idsAndOptionIds;

    public function __construct(
                                Context $context,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Catalog\Model\ProductRepository $productRepository
                        ){
        $this->_cart = $cart;
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
            $orderedChildrenProducts[$this->_idsAndOptionIds[$sku]['product_id']] = $this->_idsAndOptionIds[$sku]["option_id"];
        }
        $params = [
            'product' => $this->_idsAndOptionIds['test_bundle']['product_id'],
            'related_product' => null,
            'bundle_option' => $orderedChildrenProducts,
            'qty' => 1
        ];
        $product = $this->_productRepository->getById($this->_idsAndOptionIds['test_bundle']['product_id']);
        $this->_cart->addProduct($product,$params);
        $this->_cart->save();
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