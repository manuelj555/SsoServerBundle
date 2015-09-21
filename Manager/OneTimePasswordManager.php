<?php
/*
 * This file is part of the Manuel Aguirre Project.
 *
 * (c) Manuel Aguirre <programador.manuel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ku\SsoServerBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;
use Ku\SsoServerBundle\Entity\OneTimePassword;
use Ku\SsoServerBundle\Entity\OneTimePasswordRepository;
use Ku\SsoServerBundle\SsoEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;


/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class OneTimePasswordManager
{
    /**
     * @var ObjectManager
     */
    protected $em;
    /**
     * @var OneTimePasswordRepository
     */
    private $otpRepository;
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * OneTimePasswordManager constructor.
     *
     * @param ObjectManager             $em
     * @param OneTimePasswordRepository $otpRepository
     * @param EventDispatcherInterface  $dispatcher
     */
    public function __construct(ObjectManager $em, OneTimePasswordRepository $otpRepository, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->otpRepository = $otpRepository;
        $this->dispatcher = $dispatcher;
    }

    public function create($username)
    {
        $otp = new OneTimePassword();

        $otp->setUsername($username);
        $otp->setPassword($this->generateRandomValue());
        $otp->setUsed(false);
        $otp->setCreatedAt(new \DateTime());

        try {
            $this->em->persist($otp);
            $this->em->flush();

        } catch (DBALException $e) {
            throw new \Exception('Could not create a otp', $e->getCode(), $e);
        }

        $this->dispatcher->dispatch(SsoEvents::CREATE_OTP, new GenericEvent($otp));

        return $otp->getPassword();
    }

    /**
     * @param $password
     *
     * @return null|OneTimePassword
     */
    public function get($password)
    {
        return $this->otpRepository->findOneByPassword($password);
    }

    public function isValid(OneTimePassword $otp)
    {
        return $otp->getUsed() === false;
    }

    public function invalidate(OneTimePassword $otp)
    {
        $otp->setUsed(true);
        $this->em->flush();
    }

    protected function generateRandomValue()
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new \Exception('Could not produce a cryptographically strong random value. Please install/update the OpenSSL extension.');
        }

        $bytes = openssl_random_pseudo_bytes(64, $strong);

        if (true === $strong && false !== $bytes) {
            return base64_encode($bytes);
        }

        return base64_encode(hash('sha512', uniqid(mt_rand(), true), true));
    }
}