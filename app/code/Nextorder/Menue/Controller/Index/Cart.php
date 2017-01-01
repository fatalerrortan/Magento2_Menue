<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Cart extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_cart;
    protected $_productRepository;

    public function __construct(Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Catalog\Model\ProductRepository $productRepository){

        $this->_resultPageFactory = $resultPageFactory;
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute(){
//        $resultRedirect = $this->resultRedirectFactory->create();
//        $this->addProductsInCart();
        // refresh mini cart
//       return $resultRedirect->setPath('checkout/cart/index');

        $this->getOptionId();



        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }

    protected function addProductsInCart(){
        $params = [
            'product' => 7,
            'related_product' => null,
            'bundle_option' => [
//                1 => "19",
//                2 => "20",
                3 => "21",
                4 => "22",
                5 => "23",
//                6 => "24",
            ],
//            'options' => [
//                5 => 'Some Test value to a text field',
//            ],
            'qty' => 1
        ];
        $product = $this->_productRepository->getById("7");
        $this->_cart->addProduct($product,$params);
        $this->_cart->save();
        return true;
    }
}