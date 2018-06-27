<?php

namespace Nextorder\Menue\Controller\Checkout;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use \Nextorder\Subaccounts\Helper\Data;

class Success extends Action
{
    /**
     * @var \Nextorder\Subaccounts\Helper\Subpermissions
     */
    protected $subacchelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /** @var PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    protected $_logger;

    public $_helper;

    /**
     * Result constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        \Nextorder\Subaccounts\Helper\Subpermissions $subacchelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Psr\Log\LoggerInterface $logger,
        \Nextorder\Menue\Helper\Data $helper,
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->subacchelper = $subacchelper;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->_cart = $cart;
        $this->resultPageFactory = $pageFactory;
        $this->_logger = $logger;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * The controller action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
//        ini_set('memory_limit', '-1');
        try {
            $order = $this->_objectManager->create('Nextorder\Subaccounts\Model\Order');
            $subPermissions = $this->subacchelper->retrieveSubPermissions();
            $order->setParentId($this->customerRepository->get($subPermissions['parent'])->getId());
            $order->setStatus('pending');
            $order->setCustomerId($this->customerSession->getCustomerId());
            $order->setStoreId($this->storeManager->getStore()->getId());
            $cartItems = $this->_cart->getItems();
            $order->save();

            $itemOrder = array();
            foreach ($cartItems as $cartItem) {
                $item = $this->_objectManager->create('Nextorder\Subaccounts\Model\Order\Item');
                $item->setOrderId($order->getId());
                $item->setParentItemId($cartItem->getParentItemId());
                $item->setStoreId($cartItem->getStoreId());
                $item->setProductId($cartItem->getProductId());
                $item->setProductType($cartItem->getProductType());
                $item->setSku($cartItem->getSku());
                $item->setName($cartItem->getName());
                $item->setDescription($cartItem->getDescription());
                $item->setQtyOrdered($cartItem->getQty());

                if($cartItem->getProductType() === "bundle"){
                    $orderDetails = $cartItem->getProduct()->getTypeInstance(true)
                        ->getOrderOptions($cartItem->getProduct());
                    $optionAndSelectionIds =  $orderDetails['info_buyRequest']['bundle_option'];
                    $bundledatas = $this->_helper->getSerializedData('inc','bundleDataSource.txt');

                    foreach ($optionAndSelectionIds as $optionId => $selectionId){
                        $sku = array_search($selectionId, $bundledatas[$optionId]);
                        $date = $orderDetails['bundle_options'][$optionId]['label'];
                        $itemOrder[$date] = $sku;
                    }
                    $this->_logger->debug(print_r($itemOrder, true));
                }else{
                    $deliveryDate = array_search($cartItem->getSku(), $itemOrder);
//                    $this->_logger->debug(print_r($deliveryDate, true));
                    $item->setDeliveryDate($deliveryDate);
                    unset($itemOrder[$deliveryDate]);
                }
                $item->save();
                //remove cart item
                $itemId = $cartItem->getItemId();
                $this->_cart->removeItem($itemId)->save();
            }

            $this->messageManager->addSuccessMessage(__('Your Sub-Account order was sucessfully placed.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}