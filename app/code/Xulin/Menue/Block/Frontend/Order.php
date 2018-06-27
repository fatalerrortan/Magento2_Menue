<?php
namespace Nextorder\Menue\Block\Frontend;

class Order extends \Magento\Framework\View\Element\Template{

    protected $_productRepository;
    protected $_logger;
    protected $_customerSession;
    protected $_orderFactory;
    protected $_itemFactory;
    protected $_scopeConfig;
    protected $_productFactory;
    protected $_resultPageFactory;
    public $_orderDate = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Nextorder\Subaccounts\Model\OrderFactory $orderFactory,
        \Nextorder\Subaccounts\Model\Order\ItemFactory $itemFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        array $data = []
    ){
        $this->_productRepository = $productRepository;
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_itemFactory = $itemFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_productFactory = $productFactory;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context, $data);
    }

    public function getOrderItems(){
        $orderId = $this->getOrderId();
        if(empty($orderId)){
            return "<h1>Sie haben nichts für diese Woche bestellt.</h1>";
        }
        $itemCollection = $this->_itemFactory->create()->getCollection()
            ->addFieldToFilter('order_id', array('eq' => array($orderId)))
            ->addFieldToFilter('sku', array('neq' => array(
                //bundle product sku from admin config feld
                $this->_scopeConfig->getValue('menu/menu_group_1/menu_group_1_field_1')
            )));
        return $this->getHtml($itemCollection);
    }

    public function getOrderId(){
        $begin = $this->getRequest()->getParam("begin");
        $end = $this->getRequest()->getParam("end");
        $lastWeekBegin = date('Y-m-d', $begin - (7 * 24 * 60 * 60)); // > = Monday
        $lastWeekEnd = date('Y-m-d', $end - (4 * 24 * 60 * 60));

        $orderCollection = $this->_orderFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => array($this->_customerSession->getCustomerId())))
            ->addFieldToFilter('created_at', array('gteq' => array($lastWeekBegin)))
            ->addFieldToFilter('created_at', array('lteq' => array($lastWeekEnd)))
            ->setOrder('created_at','ASC')->load();
//        $this->_logger->addDebug(print_r($orderCollection->getData()[0]['created_at'], true));
        if(empty($orderCollection->getData())){
            return null;
        }
        $this->_orderDate = $orderCollection->getData()[0]['created_at'];
        return $orderCollection->getData()[0]['entity_id'];
    }

    public function getHtml($itemCollection){
        $tableBody = null;
        try{
            foreach ($itemCollection as $item){
                $product = $this->_productFactory->create()->loadByAttribute('sku', $item->getData('sku'));
                $productName = $product->getName();
                $productPrice = $product->getPrice();
                $imgUrl = $this->getUrl('pub/media/catalog').'product'.$product->getImage();
                $productShortDescription = $product->getShortDescription();
                $deliveryDate = $item->getDeliveryDate();

                $tableBody .= "<tr>
                        <td class='menue_tag'>
                            <b>".$deliveryDate."</b>
                        </td>
                        <td class='img_container'>
                            <img src='".$imgUrl."' scrset='".$imgUrl."' alt='".$productName."' width='200px' height='200px' />
                            <h5>".$productName."</h5>
                        </td> 
                        <td class='product_info'>
                            <div class='product_content'>
                                <span>".$productShortDescription."</span>
                            </div>
                        </td>
                        <td class='product_price'>
                            <span>".$productPrice."&euro;</span>
                        </td>  
                    </tr>";
            }
        }catch (\Exception $e){
            $this->_logger->addDebug(print_r($e->getMessage(), true));
        }
        return "<table border='1'>".$tableBody."</table>";
    }

    public function getLiveSkus(){
        $liveSkus = $this->getRequest()->getParam("skus_in_stock");
        return $liveSkus;
    }
}