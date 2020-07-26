<?php

namespace Traits;

use Behat\Mink\Session;
use Exception;

trait HelperTrait {


    /**
     * Generate random string
     *
     * @param int $length
     * @return string
     */
    public function randomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters) - 1;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength)];
        }
        return $randomString;
    }

    /**
     * @param int $length
     * @return string
     */
    protected function randomEmail($length = 7)
    {

        $generateString = function () use ($length) {
            $randomString = '';
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz+-._';
            $charactersLength = strlen($characters) - 1;
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength)];
            }
            return $randomString;
        };
        do {
            $randomEmail = $generateString() . '@example.com';
        } while (empty(filter_var($randomEmail, FILTER_VALIDATE_EMAIL)));
        return $randomEmail;
    }

    /**
     * Helper function to return desired field
     *
     * @param $target
     * @return string|null
     * @throws Exception
     */
    public function getFullIDbyPartialFirstPart($target) {
        /** @var Session $session */
        $session = $this->getSession();
        $page = $session->getPage();
        $field_to_be_return = $page->find('css','[id^="'.$target.'"]');
        if (is_null($field_to_be_return)) {
            throw new Exception("Something went wrong. Have you provided me a proper id?");
        }
        else if (is_null($field_to_be_return->getAttribute('id'))) {
            throw new Exception("Something went wrong miserably. Have you provided me proper id?!?");
        }
        else return $field_to_be_return->getAttribute('id');
    }
}