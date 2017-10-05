<?php


namespace Nextorder\Menue\Controller\Validate;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action{
    protected $_logger;

    public function __construct(Context $context,
                                \Psr\Log\LoggerInterface $logger){
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute(){
        $this->_logger->addDebug(print_r($this->getRequest()->getParams(), true));
        echo 'incorrect';
    }
}