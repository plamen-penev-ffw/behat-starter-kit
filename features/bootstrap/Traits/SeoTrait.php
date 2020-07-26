<?php

namespace Traits;
use Behat\Mink\Session as Session;

trait SeoTrait {

    /**
     * This function returns string with page title.
     * @return mixed
     */
    public function getPageTitle()
    {
        /** @var Session $session */
        $session = $this->getSession();
        return $session->evaluateScript("return document.title");
    }

    /**
     * This function returns string with page meta data for the given tag in the $metatag variable.
     * @param $metatag
     * @return mixed
     */
    public function getPageMetaData($metatag)
    {
        /** @var Session $session */
        $session = $this->getSession();
        return $result = $session->getPage()->find(
            'xpath',
            '//head/meta[@name="' . $metatag . '"]'
        )->getAttribute('content');
    }

    /**
     * This function returns string with page meta data for the given OG tag in the $og_property variable.
     * @param $og_property
     * @return mixed
     */
    public function getPageMetaDataOg($og_property)
    {
        /** @var Session $session */
        $session = $this->getSession();
        return $result = $session->getPage()->find(
            'xpath',
            '//head/meta[@property="' . $og_property . '"]'
        )->getAttribute('content');
    }

    /**
     * This function returns string with page meta data for the given link type in the $link_type variable.
     * @param $link_type
     * @return mixed
     */
    public function getPageMetaLinks($link_type)
    {
        /** @var Session $session */
        $session = $this->getSession();
        return $result = $session->getPage()->find(
            'xpath',
            '//head/link[@rel="' . $link_type . '"]'
        )->getAttribute('href');
    }
}