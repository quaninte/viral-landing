<?php

namespace BK\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

class DefaultController extends Controller
{

    /**
     * Render landing page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $businessesCount = $this->container->get('bk_core.contact_manager')->countTotal();

        return $this->render('BKCoreBundle:Default:index.html.twig', array(
            'refCode' => $request->get('ref'),
            'businessesCount' => $businessesCount,
            'showSumo' => $request->query->get('show_sumo'),
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

        // validate email
        $errors = $this->get('validator')->validateValue($email, new EmailConstraint());
        if ($errors->count()) {
            $this->container->get('session')->getFlashBag()->set('danger', 'Provided email is incorrect, please try again');
            return $this->redirect($this->generateUrl('bk_core_homepage') . '#sign-up');
        }

        $contactManager = $this->container->get('bk_core.contact_manager');

        // find old contact
        $contact = $contactManager->findByEmail($email);

        // if not found -> create
        if (!$contact) {

            // limit ip address
            $ipAddress = $request->getClientIp();
            $maxSignUpPerIp = 10;
            if ($contactManager->countByIp($ipAddress) >= $maxSignUpPerIp) {
                $this->container->get('session')
                    ->getFlashBag()
                    ->set('error', 'Sorry, we already got 10 sign ups from this ip address, please try again later');
                return $this->redirect($this->generateUrl('bk_core_homepage'));
            }

            $contact = $contactManager->createNew($email, $request->request->get('referrer'), $ipAddress);
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

        $invited = $contactManager->countInvited($contact);

        return $this->render('@BKCore/Default/submitted.html.twig', array(
            'contact' => $contact,
            'invited' => $invited,
        ));
    }

}
