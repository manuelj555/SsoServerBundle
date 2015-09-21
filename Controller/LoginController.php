<?php

namespace Ku\SsoServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class LoginController extends Controller
{
    public function loginAction(Request $request)
    {
        // Verificamos que esté logueado
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY);

        $targetPathParameter = '_target_path';

        $targetPath = $request->get($targetPathParameter);

        if (!$targetPath) {
            throw new BadRequestHttpException("Target path not specified");
        }

        if (!$this->get('ku_sso_server.security.domain_checker')->isRegistered($targetPath)) {
            throw new BadRequestHttpException("Target path is not registered");
        }

        $otp = $this->get('ku_sso_server.one_time_password_manager')->create(
            $this->getUser()->getUsername()
        );

        return $this->createRedirectResponse($request, $targetPath, $otp);
    }

    public function logoutAction()
    {
        return new Response('Login');
    }

    protected function createRedirectResponse(Request $request, $targetPath, $otp)
    {
        return $this->createSignedRedirectResponse(
            $request,
            $this->createWrappedTargetPath($targetPath, $otp)
        );
    }

    protected function createWrappedTargetPath($targetPath, $otp)
    {
        return sprintf('%s&%s=%s', $targetPath, '_otp', rawurlencode($otp));
    }

    protected function createSignedRedirectResponse(Request $request, $path, $status = 302)
    {
        return $this->get('security.http_utils')->createRedirectResponse(
            $request, $this->get('ku_sso_server.uri_signer')->sign($path), $status
        );
    }
}
