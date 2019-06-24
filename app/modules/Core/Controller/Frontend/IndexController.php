<?php
namespace App\Module\Core\Controller\Frontend;

use App\System\App;

class IndexController extends BaseController {

    public function indexAction()
    {
        App::get()->getProfiler()->start("App::Core::IndexController::indexAction");
        App::get()->getProfiler()->stop("App::Core::IndexController::indexAction");
        return $this->apiResponse(true,[
            "version" => "0.0.1"
        ]);
    }

}