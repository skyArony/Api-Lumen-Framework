<?php

namespace App\Http\Controllers\Passport;

use App\Http\Controllers\ApiController;

class UserInfo extends ApiController
{
    public function getEverySysInfo() {
        $info = array(
            "edu" => $eduInfoArray,
            "library" => $libraryArray
        );

        // 假数据先在这里面填
        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }
}



?>