<?php


namespace myapp;


class ProtectedApiController extends \Micro\SecureApiController
{

    /**
     * @inheritDoc
     */
    protected function validateToken($user, $token)
    {
        return $token=="111";
    }

    protected function loadSession()
    {
        // TODO: Implement loadSession() method.
    }
}