<?php

namespace app\api\controller;

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 统一入口
 *
 */

use think\Controller;

class ApiCommon extends  Controller
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
    }
}
