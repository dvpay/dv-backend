<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;

class DataHelper extends Module
{
    /**
     * @throws ModuleException
     */
    public function login($email, $password): string
    {
        $rest = $this->getModule('REST');
        $response = $rest->sendPost('/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response = json_decode($response);
        $authKey = $response->result->token;

        $rest->haveHttpHeader('Authorization', 'Bearer ' . $authKey);

        return $authKey;
    }

    /**
     * @throws ModuleException
     */
    public function authKey($email, $password): string
    {
        $rest = $this->getModule('REST');
        $response = $rest->sendPost('/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response = json_decode($response);
        $authKey = $response->result->token;

        $rest->haveHttpHeader('Authorization', 'Bearer ' . $authKey);

        return $authKey;
    }
}