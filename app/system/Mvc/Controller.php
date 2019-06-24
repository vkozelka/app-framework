<?php
namespace App\System\Mvc;

use App\System\App;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Controller {

    const FLASH_SUCCESS = "success";
    const FLASH_INFO = "info";
    const FLASH_ERROR = "danger";

    public function __construct()
    {
    }

    /**
     * @return View
     */
    public function getView()
    {
        return App::get()->getView();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return App::get()->getRequest();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return App::get()->getResponse();
    }

    public function apiResponse($success = true, $data = []) {
        $data["success"] = $success;
        $code = 200;
        if (isset($data["code"])) {
            $code = $data["code"];
        }
        return (new JsonResponse($data, $code));
    }

    public function exitApplication(Response $response) {
        $response->sendHeaders();
        $response->sendContent();
        exit;
    }

    public function getRouteParam($key, $default = null)
    {
        $routeParams = App::get()->getRouter()->getMatchedRoute();
        return isset($routeParams[$key]) ? $routeParams[$key] : $default;
    }

    public function redirect(string $routeName, array $routeParams = []) {
        return new RedirectResponse(App::get()->getUrl()->generate($routeName, $routeParams));
    }

    public function flash(string $message, string $type = "success") {
        App::get()->getSession()->getFlashBag()->set($type, $message);
    }

}