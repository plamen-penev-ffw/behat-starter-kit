<?php

namespace Traits;


use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session as MinkSession;

trait DebugTrait {

    /**
     * Pause the test and wait for user iteration before continuing.
     *
     * @Then I put a breakpoint
     * @Then I break
     */
    public function breakpoint() {
        fwrite(STDOUT, "\033[s \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {
        }
        fwrite(STDOUT, "\033[u");
        return;
    }

    /**
     * Execute custom JS
     *
     * @Then I execute custom JS script :script
     * @param string $script
     * @throws DriverException
     */
    public function executeCustomJS($script) {
        /** @var MinkSession $session */
        $session = $this->getSession();
        try {
            $session->getDriver()->executeScript($script);
        } catch (DriverException $e) {
            throw new DriverException($e->getMessage());
        }
    }

    /**
     * Saving the HTML source of the page in in .html file. Used for debugging purposes.
     *
     * @Then show me the page
     * @throws DriverException
     */
    public function show_me_the_page() {
        $name = "behat-" . date('Y-m-d-H-i-s') . ".html";
        /** @var MinkSession $session */
        $session = $this->getSession();
        $html = $session->getDriver()->getContent();
        fopen ($name, "a+");
        file_put_contents($name, $html);
    }

    /**
     * Take a screenshot.
     *
     * @When I take a screenshot :name
     * @param $name
     * @throws DriverException
     */
    public function takeScreenshot($name) {
        /** @var MinkSession $session */
        $session = $this->getSession();

        file_put_contents("screenshots/".$name.'_'.date('His').'.png', $session->getDriver()->getScreenshot());
    }

    /** Prints the RAM usage
     * @Then /^I print the RAM usage/
     */
    public function printRAMUssage() {
        var_dump(memory_get_usage());
    }

    /**
     * Visit the current page and add some text after the current URL.
     * For example when you are on node view page and want to visit node/edit page.
     *
     * @When I see the current URL
     */
    public function showMeCurrentUrl() {
        /** @var MinkSession $session */
        $session = $this->getSession();
        echo "You are on: ".$session->getCurrentUrl();
    }

    /**
     * Print page information to the console
     *
     * @Then  print current window
     * @throws DriverException
     */
    public function iPrintCurrentWindow() {
        /** @var MinkSession $session */
        $session = $this->getSession();
        $current_window = $session->getDriver()->getWindowName();
        var_dump($current_window);
    }

    /**
     * Maximize window
     *
     * @When I maximize window
     * @throws DriverException
     */
    public function maximizeWindow() {
        /** @var MinkSession $session */
        $session = $this->getSession();
        $session->getDriver()->maximizeWindow();
    }

    /**
     * Maximize window
     *
     * @When I resize window to :width x :height
     * @param int $width
     * @param $height
     */
    public function resizeWindowTo($width, $height) {
        /** @var MinkSession $session */
        $session = $this->getSession();
        $session->resizeWindow((int)$width, (int)$height, 'current');
    }

}
