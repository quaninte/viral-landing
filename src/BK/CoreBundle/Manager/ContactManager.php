<?php
/**
 * Copyright Brodev Software.
 * (c) Quan MT <quanmt@brodev.com>
 */


namespace BK\CoreBundle\Manager;


use BK\CoreBundle\Entity\Contact;
use Doctrine\ORM\EntityManager;

class ContactManager
{

    /** @var  EntityManager */
    protected $em;

    /**
     * Constructor
     * @param $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Get repository
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->em->getRepository('BKCoreBundle:Contact');
    }

    /**
     * Find contact by email
     * @param $email
     * @return null|object
     */
    public function findByEmail($email)
    {
        return $this->getRepository()
            ->findOneBy(array(
                'email' => $email,
            ));
    }

    /**
     * Find contact by code
     * @param $code
     * @return null|Contact
     */
    public function findByCode($code)
    {
        return $this->getRepository()
            ->findOneBy(array(
                'code' => $code,
            ));
    }

    /**
     * Create new contact
     * @param $email
     * @param $refCode
     * @return \BK\CoreBundle\Entity\Contact
     */
    public function createNew($email, $refCode)
    {
        $contact = new Contact();
        $contact->setEmail($email);
        $contact->setRefCode($refCode);

        if ($refContact = $this->findByCode($refCode)) {
            $contact->setRefContact($refContact);
        }
        $contact->setCode($this->generateCode());

        $this->em->persist($contact);
        $this->em->flush();

        return $contact;
    }

    /**
     * Generate a random code
     * @param int $length
     * @return string
     */
    protected function generateCode($length = 7)
    {
        while (true) {
            $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $code = '';
            $count = strlen($charset);
            while ($length--) {
                $code .= $charset[mt_rand(0, $count-1)];
            }

            if (!$this->findByCode($code)) {
                break;
            }
        }

        return $code;
    }

    /**
     * Get position of a contact
     * @param Contact $contact
     * @return int
     */
    public function getPosition(Contact $contact)
    {
        $count = $this->getRepository()
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.created < :created')
            ->setParameter('created', $contact->getCreated())
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * Count how many contact had a contact invited
     * @param Contact $contact
     * @return int
     */
    public function countInvited(Contact $contact)
    {
        $count = $this->getRepository()
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.refContact = :refContact')
            ->setParameter('refContact', $contact)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

} 