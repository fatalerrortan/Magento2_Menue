<?php
namespace Nextorder\Menue\Block\Adminhtml;

class Index extends \Magento\Framework\View\Element\Template{

    public $test = 'kidding!!!';
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }


}