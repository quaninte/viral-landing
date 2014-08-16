<?php

namespace BK\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('BKCoreBundle:Default:index.html.twig');
    }
}
