<?php
namespace App\Module\Core\Controller\Admin;

use App\System\App;
use App\System\Mvc\Controller;

class BaseController extends Controller {

    protected $_needAdminLogged = true;

    public function initialize() {
        if ($this->_needAdminLogged === true && !App::get()->getAdminUser()) {
            return $this->redirect("admin");
        }
    }

}