<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 10/20/14
 * Time: 2:07 PM
 * To change this template use File | Settings | File Templates.
 */

namespace BK\CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    public function loginAction(Request $request)
    {
        return $this->render('@BKCore/Login/login.html.twig');
    }

    public function signUpAction()
    {
        return $this->render('@BKCore/Login/sign_up.html.twig');
    }
}