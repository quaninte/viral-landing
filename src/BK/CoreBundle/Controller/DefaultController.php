<?php

namespace BK\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * Render landing page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        return $this->render('BKCoreBundle:Default:index.html.twig', array(
            'refCode' => $request->get('ref'),
        ));
    }

    /**
     * Handle after submit form
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitAction(Request $request)
    {
        if (!$email = $request->request->get('email')) {
            $this->container->get('session')->getFlashBag()->set('error', 'Please enter your email');
            return $this->redirect($this->generateUrl('bk_core_homepage'));
        }

        $contactManager = $this->container->get('bk_core.contact_manager');

        // find old contact
        $contact = $contactManager->findByEmail($email);

        // if not found -> create
        if (!$contact) {
            $contact = $contactManager->createNew($email, $request->request->get('referrer'));
        }

        return $this->redirect($this->generateUrl('bk_core_thank', array(
            'code' => $contact->getCode(),
        )));
    }

    /**
     * Thank you action
     * @param $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function thankAction($code)
    {
        $contactManager = $this->container->get('bk_core.contact_manager');

        $contact = $contactManager->findByCode($code);

        if (!$contact) {
            return $this->redirect($this->generateUrl('bk_core_homepage'));
        }

        $position = $contactManager->getPosition($contact);
        $invited = $contactManager->countInvited($contact);

        // fake number
        $fakeNumber = 693;
        $position += $fakeNumber;

        return $this->render('@BKCore/Default/submitted.html.twig', array(
            'contact' => $contact,
            'position' => $position,
            'invited' => $invited,
        ));
    }

}
