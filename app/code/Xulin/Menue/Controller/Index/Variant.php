<?php


namespace Nextorder\Menue\Controller\Index;

use Magento\Framework\App\Action\Context;
//In Magento 2 every action has its own class which implements the execute() method.

class Variant extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory){

        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute(){

//        $mainOrders = [mo1, mo2, mo3 …….mon];
//        $sideOrders = [so1, so2, so3 ...., son];
//        $goals = [g1, g2, g3 …...gn];
//
//        $result = [];
//        foreach ($goals as $goal) {
//            $goalType = $goal['goalType']; // main oder or side order ?
//            $itemName = $goal['itemName'];
//            $itemAmount = $goal['itemAmount'];
//            $amount = null;
//            $ordersToCheck = null;
//
//            if ($goalType == 'mainOrder') {
//                $ordersToCheck = $mainOrders;
//            } else {
//                $ordersToCheck = $sideOrders;
//                if(empty($ordersToCheck)){
//                    $result[$goalType][] = [
//                        'item' => $itemName,
//                        'supplement' => $itemAmount - $amount
//                    ];
//                    continue;
//                }
//            }
//
//            foreach ($ordersToCheck as $order) {
//                if (($order['animalFood'] == $itemName)
//                    ||
//                    ($order['plantFood'] == $itemName)
//                ) {
//                    $amount++;
//                }
//            }
//            if ($amount < $itemAmount) {
//                $result[$goalType][] = [
//                    'item' => $itemName,
//                    'supplement' => $itemAmount - $amount
//                ];
//            }
//        }
    }
}