<?php
namespace Nextorder\Menue\Block\Frontend;

class Order extends \Magento\Framework\View\Element\Template{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, //parent block injection
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

}