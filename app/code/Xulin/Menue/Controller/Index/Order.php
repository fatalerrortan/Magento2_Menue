<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Order extends \Magento\Framework\App\Action\Action{

    protected $_productRepository;
    protected $_logger;
    protected $_customerSession;
    protected $_orderFactory;
    protected $_itemFactory;
    protected $_scopeConfig;
    protected $_productFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Nextorder\Subaccounts\Model\OrderFactory $orderFactory,
        \Nextorder\Subaccounts\Model\Order\ItemFactory $itemFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ){
        $this->_productRepository = $productRepository;
        $this->_logger = $logger;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_itemFactory = $itemFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_productFactory = $productFactory;
        parent::__construct($context);
    }

    public function execute(){
        $begin = $this->getRequest()->getParam("begin");
        $end = $this->getRequest()->getParam("end");
        $lastWeekBegin = date('Y-m-d', $begin - (7 * 24 * 60 * 60)); // > = Monday
        $lastWeekEnd = date('Y-m-d', $end - (4 * 24 * 60 * 60));
        $orderId = $this->getOrderId($lastWeekBegin, $lastWeekEnd);
        if(empty($orderId)){
            echo "<table><tr><td><h1>Sie haben nichts für diese Woche bestellt.</h1></td></tr></table>";
        }else{
            $orderItemCollection = $this->getOrderItemCollection($orderId);
            $html = $this->getItemsHtml($orderItemCollection);
            echo $html;
        }
    }

    public function getOrderId($lastWeekBegin, $lastWeekEnd){
        $orderCollection = $this->_orderFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => array($this->_customerSession->getCustomerId())))
            ->addFieldToFilter('created_at', array('gteq' => array($lastWeekBegin)))
            ->addFieldToFilter('created_at', array('lteq' => array($lastWeekEnd)))
            ->setOrder('created_at','ASC')->load();
        $this->_logger->addDebug(print_r($orderCollection->getData(), true));
        if(empty($orderCollection->getData())){
            return null;

        }
        return $orderCollection->getData()[0]['entity_id'];
    }

    public function getOrderItemCollection($orderId){
        $itemCollection = $this->_itemFactory->create()->getCollection()
            ->addFieldToFilter('order_id', array('eq' => array($orderId)))
            ->addFieldToFilter('sku', array('neq' => array(
                //bundle product sku from admin config feld
                $this->_scopeConfig->getValue('menu/menu_group_1/menu_group_1_field_1')
            )));
        return $itemCollection;
    }

    public function getItemsHtml($itemCollection){
        $week = array(
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoche',
            4 => 'Donnerstag',
            5 => 'Freitag'
        );
        $index = 1;
        $tableBody = null;
        foreach ($itemCollection as $item){
            $product = $this->_productFactory->create()->loadByAttribute('sku', $item->getData('sku'));
            $productName = $product->getName();
            $productPrice = $product->getPrice();
            $imgUrl = $this->_url->getUrl('pub/media/catalog').'product'.$product->getImage();
            $productShortDescription = $product->getShortDescription();

            $tableBody .= "<tr>
                        <td class='menue_tag'>
                            <b>".$week[$index]."</b>
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
        $index++;
        }
        return "<table border='1'>".$tableBody."</table>";
    }
}