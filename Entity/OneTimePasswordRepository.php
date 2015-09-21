<?php

namespace Ku\SsoServerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * OneTimePasswordRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OneTimePasswordRepository extends EntityRepository
{
    public function findOneByHash($hash)
    {
        return $this->findOneBy(array('hash' => $hash));
    }
    public function findOneByPassword($password)
    {
        return $this->findOneBy(array('password' => $password));
    }

    public function hashExists($hash)
    {
        return $this->createQueryBuilder('otp')
            ->select('COUNT(otp.id)')
            ->where('otp.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
