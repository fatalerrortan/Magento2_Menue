<?php
/**
 * Created by PhpStorm.
 * User: fatalerrortxl
 * Date: 23.12.16
 * Time: 15:15
 */
namespace Nextorder\Menue\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getAdminConfig(){
        return array(
            1 => 'test_sku_01',
            2 => 'test_sku_02',
            3 => 'test_sku_03',
            4 => 'test_sku_04',
            5 => 'test_sku_05',
            6 => 'test_sku_06'
        );
    }
}