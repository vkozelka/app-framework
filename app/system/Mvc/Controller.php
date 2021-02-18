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

    protected function apiResponse($success = true, $data = []): Response {
        $data["success"] = $success;
        $code = 200;
        if (isset($data["code"])) {
            $code = $data["code"];
        }

        $data["profiler"] = App::get()->outputProfiler(true);

        $response = new JsonResponse($data, $code);
        $response->headers->add(['content-type' => 'application/json']);
        return $response;
    }

    protected function exitApplication(Response $response) : void {
        $response->sendHeaders();
        $response->sendContent();
        exit;
    }

    protected function getRouteParam($key, $default = null) : ?string
    {
        $routeParams = App::get()->router->getMatchedRoute();
        return isset($routeParams[$key]) ? $routeParams[$key] : $default;
    }

    protected function redirect(string $routeName, array $routeParams = []): RedirectResponse {
        return new RedirectResponse(App::get()->url->generate($routeName, $routeParams));
    }

    protected function flash(string $message, string $type = "success"): void {
        App::get()->session->getFlashBag()->set($type, $message);
    }

}