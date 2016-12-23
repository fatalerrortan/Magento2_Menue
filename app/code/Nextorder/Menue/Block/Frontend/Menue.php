<?php
namespace Nextorder\Menue\Block\Frontend;

class Menue extends \Magento\Framework\View\Element\Template
{
//    load custom helper in block class
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Nextorder\Menue\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }


    public function getHelloWorldTxt(){

        return 'Hello world!';
    }
}