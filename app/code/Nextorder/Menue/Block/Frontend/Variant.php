<?php

namespace Nextorder\Menue\Block\Frontend;

class Variant extends \Magento\Framework\View\Element\Template
{

    protected $_logger;
    public $_helper;
    protected $_productCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        \Nextorder\Menue\Helper\Data $helper, //helper injection
        \Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Catalog\Model\ProductFactory $productCollection, //product collection injection
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_productCollection = $productCollection->create();
        parent::__construct($context, $data);
    }

    public function fottest(){
        return "213712983712192721";
    }
}