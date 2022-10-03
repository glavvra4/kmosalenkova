<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends FrontendController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function defaultAction(Request $request): Response
    {
        $linkHelper = $this->view->getHelper("HeadLink");
        $linkHelper->setCacheBuster(false);

        $scriptHelper = $this->view->getHelper("HeadScript");
        $scriptHelper->setCacheBuster(false);

        return $this->render('default/default.html.twig');
    }
}
