<?php


namespace Nextorder\Menue\Controller\Index;
use Magento\Framework\App\Action\Context;

class Order extends \Magento\Framework\App\Action\Action{

    protected $_logger;
//    protected $_resultJsonFactory;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute(){

    }
}