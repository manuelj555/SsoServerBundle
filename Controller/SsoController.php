<?php

namespace Ku\SsoServerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SsoController extends Controller
{
    /**
     * @param Request $request
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return JsonResponse
     */
    public function authenticateAction(Request $request)
    {
        $token = $this->get('security.token_storage')->getToken();

        $data = serialize(array(
            'username' => $token->getUsername(),
            'attributes' => $token->getAttributes(),
        ));

        $encrypted = $this->get('ku_sso_server.security.encrypter')->encrypt($data);

        return new Response($encrypted);
    }
}
