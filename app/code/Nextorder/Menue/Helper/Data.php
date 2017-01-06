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
            'test_sku_01',
            'test_sku_02',
            'test_sku_03',
            'test_sku_04',
            'test_sku_05'
        );
    }
}