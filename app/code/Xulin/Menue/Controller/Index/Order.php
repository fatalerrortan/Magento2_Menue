<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Order extends \Magento\Framework\App\Action\Action{

    protected $_cart;
    protected $_productRepository;
    protected $_logger;
//    protected $_resultJsonFactory;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute(){
        echo 'test';
//        if ($this->getRequest()->getParam("menu_orders")){
//            echo "123";
//        }
    }

//    protected function addProductsInCart(){
//        $params = [
//            'product' => 7,
//            'related_product' => null,
//            'bundle_option' => [
////                1 => "19",
////                2 => "20",
//                3 => "21",
//                4 => "22",
//                5 => "23",
////                6 => "24",
//            ],
//            'qty' => 1
//        ];
//        $product = $this->_productRepository->getById("7");
//        $this->_cart->addProduct($product,$params);
//        $this->_cart->save();
//        return true;
//    }
}