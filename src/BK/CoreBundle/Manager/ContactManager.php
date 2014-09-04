<?php
/**
 * Copyright Brodev Software.
 * (c) Quan MT <quanmt@brodev.com>
 */


namespace BK\CoreBundle\Manager;


use BK\CoreBundle\Entity\Contact;
use Doctrine\ORM\EntityManager;
use Segment;

class ContactManager
{

    /** @var  EntityManager */
    protected $em;

    /**
     * @var string
     */
    protected $segmentIOWriteKey;

    /**
     * Constructor
     * @param \Doctrine\ORM\EntityManager $em
     * @param $segmentIOWriteKey
     */
    public function __construct(EntityManager $em, $segmentIOWriteKey)
    {
        $this->em = $em;
        $this->segmentIOWriteKey = $segmentIOWriteKey;
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
     * Find contact by email
     * @param $ipAddress
     * @return null|object
     */
    public function countByIp($ipAddress)
    {
        $count = $this->getRepository()
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.ip = :ipAddress')
            ->setParameter('ipAddress', $ipAddress)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
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
     * @param null $ip
     * @return \BK\CoreBundle\Entity\Contact
     */
    public function createNew($email, $refCode, $ip = null)
    {
        $contact = new Contact();
        $contact->setEmail($email);
        $contact->setRefCode($refCode);
        $contact->setIp($ip);

        if ($refContact = $this->findByCode($refCode)) {
            $contact->setRefContact($refContact);

            // segment io
            if ($this->segmentIOWriteKey) {
                Segment::init($this->segmentIOWriteKey);
                Segment::identify(array(
                    'userId' => $refContact->getId(),
                    'traits' => array(
                        'invited' => $this->countInvited($refContact) + 1,
                        'email' => $refContact->getEmail(),
                        'code' => $refContact->getCode(),
                    )
                ));
                Segment::track(array(
                    'userId' => $refContact->getId(),
                    'event' => 'Complete invited',
                    'properties' => array(
                        'email' => $email,
                    )
                ));
            }
        }
        $contact->setCode($this->generateCode());
        $contact->setPosition($this->getPosition($contact));

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
        $code = null;

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

        $fakeNumber = 693;
        $count += $fakeNumber;

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

    /**
     * Count how many contacts signed up
     * @return int
     */
    public function countTotal()
    {
        $count = $this->getRepository()
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->useResultCache(true)
            ->setResultCacheLifetime(60 * 5)
            ->getSingleScalarResult();

        return $count;
    }

} 