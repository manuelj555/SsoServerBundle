<?php
/*
 * This file is part of the Manuel Aguirre Project.
 *
 * (c) Manuel Aguirre <programador.manuel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ku\SsoServerBundle\Security;

use Ku\SsoClientBundle\Security\UserDataEncrypter;
use Ku\SsoServerBundle\Manager\OneTimePasswordManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;


/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class OneTimePasswordAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var OneTimePasswordManager
     */
    private $otpManager;

    /**
     * OneTimePasswordAuthenticator constructor.
     *
     * @param OneTimePasswordManager $otpManager
     */
    public function __construct(OneTimePasswordManager $otpManager) { $this->otpManager = $otpManager; }


    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $otp = $token->getCredentials();

        $oneTimePassword = $this->otpManager->get($otp);

        if (!$oneTimePassword) {
            throw new AuthenticationException("Otp is not found");
        }

        if (!$this->otpManager->isValid($oneTimePassword)) {
            throw new AuthenticationException("Otp is not valid");
        }

        $user = $userProvider->loadUserByUsername($oneTimePassword->getUsername());

        $this->otpManager->invalidate($oneTimePassword);

        $token = new PreAuthenticatedToken($user, $otp, $providerKey, array('ROLE_USER'));

        return $token;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken and $token->getProviderKey() == $providerKey;
    }

    public function createToken(Request $request, $providerKey)
    {
        if (!$otp = $request->get('_otp')) {
            throw new BadRequestHttpException('Otp parameter is missing');
        }

        return new PreAuthenticatedToken('sso.', $otp, $providerKey);
    }
}