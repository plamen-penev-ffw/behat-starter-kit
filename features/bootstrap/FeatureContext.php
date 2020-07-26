<?php

namespace Contexts;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Element\Element;
use Behat\Mink\Session;
use Behat\Behat\Context\Context;
use DateTime;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as Assertions;
use Traits\JsonTrait;
use Traits\DateAndTimeTrait;
use Traits\DebugTrait;
use Traits\IpsumTrait;
use Traits\HelperTrait;
use Traits\SeoTrait;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Driver\Selenium2Driver;
use ZipArchive;


/**
* Features context.
*
* @author Bozhidar Boshnakov <bboshnakov91@gmail.com> and awesome QA Team
*/
class FeatureContext extends MinkContext implements Context {


   // Used in steps related to saving the href of a provided text and then visiting this href..
    public $href = null;
   // Contains all parameters passed from the YML profile.
    public $params = array();


    //Original window name variable
    public $originalWindowName;

   // Add usage for different Traits
   use JsonTrait;
   use DateAndTimeTrait;
   use DebugTrait;
   use IpsumTrait;
   use HelperTrait;
   use SeoTrait;

   protected $headers;

   public function __construct(array $parameters = null) {

       $this->params = $parameters;

       ini_set('date.timezone', 'Europe/Sofia');
       date_default_timezone_set('Europe/Sofia');
   }

   /**
    * Push a Submit, Delete, Run etc. button.
    *
    * @Given I push the :button button
    * @param $button
    * @throws Exception
    */
   public function iPushTheButton($button) {

      $buttons = array(
        'Submit' => 'edit-submit',
        'Delete' => 'edit-delete',
        'Run' => 'edit-run',
        'Cancel' => 'edit-cancel',
        'Deploy' => 'edit-deploy',
        'Apply' => 'edit-submit-admin-views-user',
        'Index50' => 'edit-cron',
        'SubmitFilters' => 'edit-filters-submit',
        'SubmitPayment' => 'btnAuthSubmit',
        'Confirm' => 'edit-confirm',
        'Recipe' => 'btn_continue',
        'Finish' => 'edit-return',
        'Save' => 'panels-ipe-save'
    );

    if (!isset($buttons[$button])) {
      throw new InvalidArgumentException(sprintf('"%s" button is not mapped. Map the button in your function.', $button));
    }

    $this->getSession()->getPage()->pressButton($buttons[$button]);
  }

   /**
    * Waiting for text to appear on a page with certain max execution time.
    *
    * @When /I wait for text "([^"]*)" to appear with max time "([^"]+)"(?: seconds)?/
    * @param $text
    * @param $maxExecutionTime
    * @throws Exception
    */
   public function iWaitForTextToAppearWithMaxTime($text, $maxExecutionTime) {;
    $isTextFound = false;
    for ($i = 0; $i < $maxExecutionTime; $i++) {
      try {
        $this->assertPageContainsText($text);
        $isTextFound = true;
        break;
      }
      catch (Exception $e) {
        sleep(1);
      }
    }

    if (!$isTextFound) {
      throw new Exception("'$text' didn't appear on the page for $maxExecutionTime seconds");
    }
  }

    /**
     * Waiting for reaching a specific page with certain max execution time.
     *
     * @When /I wait for "([^"]*)" seconds to reach "([^"]+)" page/
     * @param $page
     * @param $maxExecutionTime
     * @throws Exception
     */
    public function iWaitForTimeToReachPage($maxExecutionTime, $page) {;
        $amIonPage = false;
        for ($i = 0; $i < $maxExecutionTime; $i++) {
            try {
                $this->assertPageAddress($page);
                $amIonPage = true;
                break;
            }
            catch (Exception $e) {
                sleep(1);
            }
        }

        if (!$amIonPage) {
            throw new Exception("'$page' page was not reached for $maxExecutionTime seconds");
        }
    }

    /**
     * Waiting for text to disappear from a page with certain max execution time.
     *
     * @When /I wait for text "([^"]*)" to disappear with max time "([^"]+)"(?: seconds)?/
     * @param $text
     * @param $maxExecutionTime
     * @throws Exception
     */
    public function iWaitForTextToDisappearWithMaxTime($text, $maxExecutionTime) {;
        $isTextMissing = false;
        for ($i = 0; $i < $maxExecutionTime; $i++) {
            try {
                $this->assertPageContainsText($text);
                $isTextMissing = true;
                break;
            }
            catch (Exception $e) {
                sleep(1);
            }
        }

        if (!$isTextMissing) {
            throw new Exception("'$text' didn't disappear from the page for $maxExecutionTime seconds");
        }
    }

    /**
    * Click on some text.
    *
    * @When I click on the text :text
    * @param $text
    * @throws Exception
    */
    public function iClickOnTheText($text) {

        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',$session->getSelectorsHandler()->selectorToXpath(
                'xpath', '//*[contains(text(),"' . $text . '")]'));

        if(is_null($element)) {
          throw new Exception("Element not found");
        }
        $element->click();
    }

    /**
     * Click on JSON variable
     *
     * @When I click on :variable JSON variable
     * @param $variable
     * @throws Exception
     */
    public function iClickOnJSONvariable($variable) {
        $value = $this->iGetTheVariableFroMJsonFile($variable);
        $this->iClickOnTheText($value);
    }

    /**
     * Assert that you see a JSON variable on the page
     *
     * @When I should see :variable JSON variable
     * @param $variable
     * @throws Exception
     */
    public function iShouldSeeJSONvariable($variable) {
        $value = $this->iGetTheVariableFroMJsonFile($variable);
        $this->assertPageContainsText($value);
    }

    /**
     * @When I click :arg in the JSON variable :arg2 row
     * @param $link
     * @param $rowText
     * @throws Exception
     */
    public function clickOnJsonVariableRow($link, $rowText) {
        $param = $this->iGetTheVariableFroMJsonFile($rowText);
        /** @var Session $session */
        $session = $this->getSession();
        $page = $session->getPage();
        if ($link = $this->getTableRow($page, $param)->findLink($link)) {
            // Click the link and return.
            $link->click();
            return;
        }
        throw new Exception(sprintf('Found a row containing "%s", but no "%s" link on the page %s', $param, $link, $this->getSession()->getCurrentUrl()));
    }

  /**
  * Confirms the currently opened popup.
  *
  * @When I confirm the popup
  */
  public function confirmPopup() {
      /** @var Selenium2Driver $driver */
      $driver = $this->getSession()->getDriver();
      $driver->getWebDriverSession()->accept_alert();
  }

  /**
  * Cancels the currently opened popup.
  *
  * @When I cancel the popup
  */
  public function cancelPopup() {
      /** @var Selenium2Driver $driver */
      $driver = $this->getSession()->getDriver();
      $driver->getWebDriverSession()->dismiss_alert();
  }

    /**
     * This step overrides windows.confirm and basically accepts it before it is displayed.
     * Note that this is a workaround since there is no support for accepting popups in PhantomJS.
     *
     * @When I bypass the popup
     */
    public function bypassPopup() {
        $function = "
                var realConfirm=window.confirm;
                window.confirm=function(){
                    window.confirm=realConfirm;
                    return true;
                };
                    ";
        $session = $this->getSession();
        $session->executeScript($function);
    }

    /**
     * Click on the element with the provided xpath query.
     *
     * @When I click on the element with xpath :xpath
     * @param $xpath
     * @throws Exception
     */
    public function iClickOnTheElementWithXPath($xpath) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $element->click();

  }

    /**
     * Click on all elements with the provided CSS Selector using JS
     *
     * @When /^I click on all the elements with css selector "((?:[^"]|\")*)" using JS$/
     * @param $cssSelector
     */
    public function iClickOnTheElementsWithCSSSelectorUsingJS($cssSelector) {

        $javascript = "jQuery.each(jQuery('$cssSelector'), function () {
                        jQuery(this).click() });";

        $this->getSession()->executeScript($javascript);
    }


    /**
     * Store value of element with ID
     *
     * @When I store the value from the element with :cssSelector selector in :variable
     * @param $cssSelector
     * @param $variable
     * @throws Exception
     */
    public function iStoreTheValueOfTheElementWithID($cssSelector, $variable)
    {
        /** @var Session $session */
        $session = $this->getSession();
        $element = $session->getPage()->find('css', $cssSelector);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $text = $element->getValue();
        $this->iStoreVariableWithValueToTheJsonFile($variable, $text);
    }

    /**
     * Fill a field with a variable from JSON.
     *
     * @When I fill in :field with :variable variable from JSON
     * @param $field
     * @param $variable
     * @throws Exception
     */
    public function iFillInFieldWithParameter($field, $variable) {

        $value = $this->iGetTheVariableFroMJsonFile($variable);
        $this->getSession()->getPage()->fillField($field, $value);
    }

    /**
     * Set value to the element with the provided xpath query.
     *
     * @When /^I set value "([^"]*)" to the element with xpath "([^"]*)"$/
     * @param $value
     * @param $xpath
     * @throws Exception
     */
    public function iSetValueToTheElementWithXPath($value,$xpath) {

        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $element->setValue($value);
  }

    /**
     * Click on the element with the provided CSS Selector
     *
     * @When /^I click on the element with css selector "((?:[^"]|\")*)"$/
     * @param $cssSelector
     * @throws Exception
     */
    public function iClickOnTheElementWithCSSSelector($cssSelector) {

        $session = $this->getSession();
        $element = $session->getPage()->find('css', $cssSelector);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $element->click();
  }
    /**
     * Click on the element with the provided CSS Selector using JS
     *
     * @When /^I click on the element with css selector "((?:[^"]|\")*)" using JS$/
     * @param $cssSelector
     * @throws Exception
     */
    public function iClickOnTheElementWithCSSSelectorUsingJS($cssSelector) {

        $javascript = <<<SCRIPT
var elem = document.querySelector('$cssSelector');
var evt = new MouseEvent('click', {
    bubbles: true,
	cancelable: true,
	view: window
});	
elem.dispatchEvent(evt);
SCRIPT;

        try {
            $this->getSession()->executeScript($javascript);
        } catch (Exception $e) {
            $e->getMessage();
            throw new Exception('Element not found');
        }
    }



    /**
     * @When I hover over the element :locator
     * @param $locator
     * @throws Exception
     */
    public function iHoverOverTheElement($locator) {
        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find('css', $locator); // runs the actual query and returns the element
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
         // ok, let's hover it
         $element->mouseOver();
    }

    /**
     * The browser sleeps for seconds.
     *
     * @Given I sleep for :var seconds
     * @param $var
     */
    public function iSleepForSeconds($var) {
        $seconds = ((int)$var);
        sleep($seconds);
      }

    /**
     * The browser waits for seconds.
     *
     * @Given I wait for :var seconds
     * @param $var
     */
  public function iWaitForSeconds($var) {

    $seconds = ((int)$var) * 1000;
    $this->getSession()->wait($seconds);
  }

    /**
     * Opens certain page
     *
     * @Given /^I (?:am on the|go to the) "([^"]+)"(?: page)?$/
     * @param $page
     */
  public function iAmOnThe($page) {

    $pages = array(
        'homepage' => '/',
        'login'   => '/user',
        'logout' => '/user/logout',
        'register' => '/user/register',
        'contact' => '/contact',
        'blog' => '/blog',
        'search' => '/search/site',
        'add article' => '/node/add/article',
        'add place' => 'node/add/place',
        'add institution' => 'node/add/institution',
        'content' => 'admin/content',
        'cron' => 'admin/config/system/cron'
    );

    if (!isset($pages[$page])) {
      throw new InvalidArgumentException(sprintf('"%s" page is not mapped. Map the page in your function.', $page));
    }

    $this->getSession()->visit($this->locatePath($pages[$page]));
  }

    /**
     * Log in Drupal with provided username and password.
     *
     * @Given I log in as :username :password
     * @param $username
     * @param $password
     * @throws Exception
     */
  public function iLogInAs($username, $password) {

    $session = $this->getSession();
    $page = $session->getPage();
    try {
        $page->fillField("edit-name", $username);
        $page->fillField("edit-pass", $password);
        $page->pressButton("edit-submit");
    } catch (ElementNotFoundException $e) {
        throw new Exception($e->getMessage());

    }
  }

    /**
     * Visit profile's homepage with either HTTP or HTTPS
     *
     * @Given I go to :path via :protocol protocol
     * @param string $path
     * @param $protocol
     * @throws Exception
     * @internal param string $path
     * @internal param string $protocol
     */
  public function iGoToPageViaProtocol($path, $protocol) {
    $base_url = $this->getMinkParameter('base_url');
    // Remove backslash at the end of the bas_url if it exits.
    if (substr($base_url, -1) == "/") {
        $base_url = substr($base_url, 0, -1);
      }
    // Check for leading backslash in destination path.
    if (substr($path, 0, 1) != "/") {
        $path = "/".$path;
    }
    // Concatenate base_url with destination path.
    $base_url = $base_url.$path;
    if ($protocol !== 'HTTP' && $protocol !== "HTTPS") {
        throw new Exception('You are supposed to select HTTP or HTTPS as a protocol.');
    }
    else if ($protocol == 'HTTP') {
      if ($base_url[4] == ':') {
        $this->getSession()->visit($base_url);
      }
      else {
        $string = $base_url;
        $pattern = '/https/';
        $replacement = 'http';
        $urlToVisit = preg_replace($pattern, $replacement, $string);
        $this->getSession()->visit($urlToVisit);
      }
    }
    else if ($protocol == 'HTTPS') {
      if ($base_url[4] == 's') {
        $this->getSession()->visit($base_url);
      }
      else {
        $string2 = $base_url;
        $pattern2 = '/http/';
        $replacement2 = 'https';
        $urlToVisit2 = preg_replace($pattern2, $replacement2, $string2);
        $this->getSession()->visit($urlToVisit2);
      }
    }
  }


    /**
     * Scrolling element with id to the top of the page
     *
     * @When I scroll element with id :argument to the top
     * @param $elementId
     * @throws Exception
     */
    public function scrollIntoIdView($elementId) {
        $function =
        "var elem = document.getElementById('$elementId');
        elem.scrollIntoView(true);";
        try {
            $this->getSession()->executeScript($function);
        }
        catch(Exception $e) {
            throw new Exception("Probably I was not able to find an element with this id...actually I don't know what is the problem :( ");
        }
    }

    /**
     * Scrolling element with css selector to the top of the page
     *
     * @When I scroll element with :cssSelector to the top
     * @param $cssSelector
     * @throws Exception
     */
    public function iScrollElementWith($cssSelector) {
        $script = "jQuery('$cssSelector')[0].scrollIntoView();";
        try {
            $this->getSession()->executeScript($script);
        }

        catch(Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * Checks, that option from select with specified id|name|label|value is selected.
     *
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected/
     * @Then /^the option "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     * @Then /^"(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     * @param $option
     * @param $select
     * @throws ElementNotFoundException
     * @throws Exception
     */
  public function theOptionFromShouldBeSelected($option, $select) {
    $selectField = $this->getSession()->getPage()->findField($select);
    if (null === $selectField) {
      throw new ElementNotFoundException($this->getSession(), 'select field', 'id|name|label|value', $select);
    }
    $optionField = $selectField->find('named', array(
        'option',
        $option,
    ));
    if (null === $optionField) {
      throw new ElementNotFoundException($this->getSession(), 'select option field', 'id|name|label|value', $option);
    }
    if (!$optionField->isSelected()) {
      throw new Exception('Select option field with value|text "' . $option . '" is not selected in the select "'.$select.'"');
    }
  }

  /**
   * Save the one-time login URL to JSON :variable
   *
   * @When I save current page URL to JSON :variable
   * @param $variable
   */
  public function iSaveTheOneTimeLoginURL($variable) {
      $url = $this->getSession()->getCurrentUrl();
      $this->iStoreVariableWithValueToTheJsonFile($variable, $url);
  }

  /**
   * Visit the saved URL from the JSON :variable
   *
   * @When I visit the saved URL from the JSON :variable
   * @param $variable
   * @throws Exception
   */
  public function iVisitTheOneTimeLoginURL($variable) {
      $url = $this->iGetTheVariableFroMJsonFile($variable);
      $this->getSession()->visit($url);
  }

    /**
     * Visit the saved relative path from the JSON :variable
     *
     * @When I visit the saved relative path from the JSON :variable
     * @param $variable
     * @throws Exception
     */
    public function iVisitTheRelativePath($variable) {
        $base_url = $this->getMinkParameter('base_url');
        if (substr($base_url, -1) == '/') {
            // Strip trailing slash when present.
            $base_url = substr($base_url, 0, strlen($base_url) - 1);
        }

        $url = $base_url . $this->iGetTheVariableFroMJsonFile($variable);
        $this->getSession()->visit($url);
    }

    /**
     * Check the response code from the current page.
     * @When I should get HTTP response code :statusCode
     * @param $statusCode
     * @throws Exception
     */
  public function iShouldGetHttpResponseCode($statusCode) {
    $url = $this->getSession()->getCurrentUrl();
    $headers = get_headers($url, 1);
    $response = substr($headers[0],9,3);
    if ($statusCode != $response) {
      throw new Exception("The page resulted in having response code $response , but expected $statusCode.");
    }
  }

    /**
     * This steps saves the alias of the node
     *
     * @When I save path for current page
     */
    public function iSaveNodePath() {

        $current_url = $this->getSession()->getCurrentUrl();
        $path = parse_url($current_url, PHP_URL_PATH);

        $this->iStoreVariableWithValueToTheJsonFile("path", $path);
    }

    /**
     * @When I visit saved path page
     * @throws Exception
     */
    public function iVisitSavedNodePath() {
        $path = $this->iGetTheVariableFroMJsonFile('path');
        $url_to_visit = $this->getMinkParameter('base_url') . $path;
        $this->getSession()->visit($url_to_visit);
    }

    /**
     * Store node id (nid) with specific variable name to the json file
     * If you need the full path or if the website uses path auto, use the
     * step for saving the path.
     *
     * @When I store the node id with variable name :variable to the json file
     * @When I store the entity id with variable name :variable to the json file
     * @When I store the term id with variable name :variable to the json file
     * @param $variable
     * @throws Exception
     */
    public function iStoreTheNodeIdWithVariableNameToTheJsonFile($variable) {
        $current_url = $this->getSession()->getCurrentUrl();
        $path = parse_url($current_url, PHP_URL_PATH);
        $result = preg_match('/[1-9][0-9]*/', $path , $matches);
        if ($result) {
            $node_id = $matches[0];
            $this->iStoreVariableWithValueToTheJsonFile($variable, $node_id);
        } else {
            throw new Exception('There is no id in the entity path alias');
        }
    }


    /**
     * Get the href of an anchor
     *
     * @When I get the href of :text
     * @param $text
     */
    public function saveHref($text){
        $a = $this->getSession()->getPage()->findLink($text);
        if (isset($a)) {
            $this->href =  $a->getAttribute("href");
        }
    }

    /**
     * Get the href of link with css selector
     *
     * @When I get the href of <a> with selector :cssSelector and store it to json :variable
     * @param $cssSelector
     * @param $variable
     * @throws Exception
     */
    public function extractHref($cssSelector, $variable){

        $cssSelector = preg_replace_callback('/%([a-zA-Z_-]+)%/',
            array($this, 'replacePlaceholders'), $cssSelector);

        $element = $this->getSession()->getPage()->find('css', $cssSelector);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $href = $element->getAttribute("href");

        $this->iStoreVariableWithValueToTheJsonFile($variable, $href);

    }

    /**
     *
     * @param $matches
     * @throws Exception
     * @return mixed
     */
    private function replacePlaceholders($matches)
    {
        return $this->iGetTheVariableFroMJsonFile($matches[1]);
    }

    /**
     * Visit the saved href of an anchor
     *
     * @When I visit the saved href
     */
    public function visitSavedHref()
    {
        $this->getSession()->visit($this->href);
    }

    /**
     * Execute specific cron job.
     *
     * @When I execute :cron cron job
     * @param $cron
     * @throws Exception
     */
    public function iExecuteCronJob($cron) {
        $url_to_visit = "/admin/config/system/cron/jobs/".$cron."/run";
        $this->visitPath($url_to_visit);
        $this->assertPageContainsText("was successfully run.");
    }

    /**
     * Visit the current page and add some text after the current URL.
     * For example when you are on node view page and want to visit node/edit page.
     *
     * @When I go to current page plus :plus
     * @param $plus
     */
    public function iGoToCurrentPagePlus($plus) {
        $url_to_visit = $this->getSession()->getCurrentUrl();
        $url_to_visit = $url_to_visit.$plus;
        $this->getSession()->visit($url_to_visit);
    }



    /**
     * Visit the current page and add some text after the current URL.
     * For example when you are on node view page and want to visit node/edit page.
     *
     * @When I check if link :link has href :href
     * @param $link
     * @param $href
     * @throws Exception
     */
    public function assertHrefValueOfLink($link, $href) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//*[contains(text(),"' . $link . '")]'));
        $href_of_element = $element->getAttribute("href");
        if (empty($href_of_element)) {
            throw new Exception("The provided link \"".$link."\" is not actually a link or it's href is empty.");
        }
        if ($href_of_element != $href) {
            throw new Exception("The href of the link is \"".$href_of_element."\" and it is different from the expected \"".$href."\" href.");
        }
    }

    /**
     *
     * Checks if the text of the <a> tag contains provided text and visits the href link for it
     * For example you have <a href="http://google.com">This is my text</a> and you click on the a containing "is my text"
     * this will lead you to google.com
     *
     * @When I click on the a containing :text
     * @param $text
     */
    public function iClickOnTheLinkContaining($text) {
        $element = $this->getSession()->getPage()->find('xpath', '//a[contains(text(),"' . $text . '")]');
        if (null === $element) {
            throw new InvalidArgumentException(sprintf('Cannot find link with text containing: "%s"', $text));
        }
        $link = $element->getAttribute("href");
        $this->getSession()->visit($link);
    }

    /**
     * @When I assert that element with id :elementIdentifier has :attribute attribute with :value value
     * @param $elementIdentifier
     * @param $attribute
     * @param $value
     * @throws Exception
     */
    public function iAssertThatElementWithIdHasAttributeWithValue($elementIdentifier, $attribute, $value) {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', "#".$elementIdentifier);
        if (!empty($element)) {
            $valueOfAttribute = $element->getAttribute($attribute);
        }
        else {
            throw new Exception('Element with id "'.$elementIdentifier.'" was not found.');
        }
        if (empty($valueOfAttribute)) {
            throw new Exception("The element does not have ".$attribute." attribute");
        }
        if ($valueOfAttribute != $value) {
            throw new Exception("The value of ".$attribute. " is ". $valueOfAttribute . " and does not match the required " .$value." value.");
        }

    }


    /**
     * @When I bypass varnish cache for current page
     */
    public function iBypassVarnishCacheForCurrentPage(){
        $url = $this->getSession()->getCurrentUrl();
        $url = $url."?".time();
        $this->getSession()->visit($url);
    }

    /**
     * Select value from select element with xpath query.
     *
     * @When I select :select from element with xpath :xpath
     * @param $select
     * @param $xpath
     * @throws DriverException
     */
    public function iSelectFromElementWithXpath($select, $xpath) {

        $this->getSession()->getDriver()->selectOption($xpath, $select);
    }


    /**
     * Switching the perspective to the second tab/window.
     *
     * @Given I switch to window :windowNumber
     * @param $windowNumber
     * @throws Exception
     */
  public function iSwitchToNewWindow($windowNumber) {

    $windowNames = $this->getSession()->getWindowNames();
    #echo "Window names: ";
    #var_dump($windowNames);
    if(count($windowNames) > $windowNumber) {
      $this->getSession()->switchToWindow($windowNames[$windowNumber]);
    }
    else{
      throw new Exception('You request tab/window number that does not exist');
    }
  }

    /**
     * Switching the perspective to iFrame
     *
     * @Given I switch to iframe :iframe
     * @param $iframe
     * @throws DriverException
     */
    public function iSwitchToIframe($iframe) {

        $originalWindowName = $this->getSession()->getDriver()->getWindowName(); //Get the original name

        if (empty($this->originalWindowName)) {
          $this->originalWindowName = $originalWindowName;
        }

        $this->getSession()->switchToIframe($iframe);
    }

    /**
     * Switching the perspective to iFrame
     *
     * @Given I switch to iframe with name starting with :string
     * @param $string
     * @throws Exception
     */
    public function iSwitchToIframeWithName($string) {

        $session = $this->getSession();

        $originalWindowName = $session->getDriver()->getWindowName(); //Get the original name

        if (empty($this->originalWindowName)) {
            $this->originalWindowName = $originalWindowName;
        }

        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//iframe[starts-with(@name, ' . $string .')]'));

        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $iframe = $element->getAttribute('name');
        try {

            $this->getSession()->switchToIframe($iframe);
        } catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }

    /**
    * Switching the perspective to popup specific
    *
    *@Then I switch to popup specific
    * @throws Exception
    */
    public function iSwitchToPopupSpecific() {

        $originalWindowName = $this->getSession()->getDriver()->getWindowName(); //Get the original name

        if (empty($this->originalWindowName)) {
          $this->originalWindowName = $originalWindowName;
        }

        $this->getSession()->getPage()->pressButton("Withdraw"); //Pressing the withdraw button
        $popupName = $this->getNewPopup();
        //Switch to the popup Window
        $this->getSession()->switchToWindow($popupName);
    }

  /**
  * Switching the perspective to the original window
  *
  * @Then I switch back to original window
  */
  public function iSwitchBackToOriginalWindow() {
    //Switch to the original window
    $this->getSession()->switchToWindow($this->originalWindowName);
    $this->getSession()->wait(2000);
  }

    /**
     * Find that heading is not in a specified region.
     *
     * @Then /^I should not see the heading "(?P<heading>[^"]*)" in the "(?P<region>[^"]*)"(?:| region)$/
     * @Then /^I should not see the "(?P<heading>[^"]*)" heading in the "(?P<region>[^"]*)"(?:| region)$/
     *
     * @param $heading
     * @param $region
     * @throws Exception If region or header within it cannot be found.
     */
  public function assertRegionHeading($heading, $region) {

    $regionObj = $this->getRegion($region);

    foreach (array('h1', 'h2', 'h3', 'h4', 'h5', 'h6') as $tag) {
      $elements = $regionObj->findAll('css', $tag);

      if (!empty($elements)) {
        foreach ($elements as $element) {
          if (trim($element->getText()) != $heading) {
            return;
          }
        }
      }
    }

    throw new Exception("There is such heading $heading in that region $region.");
  }

    /**
     * Find that text belongs to a specified region.
     *
     * @Then I should see the :text text in the :region region
     *
     * @param $text
     * @param $region
     * @throws Exception If element with such text cannot be found.
     */
    public function assertRegionText($text, $region) {
        $storedVariable = $this->iGetTheVariableFroMJsonFile2($text);

        if (!is_null($storedVariable)) {
            $text = $storedVariable;
        } else if(isset($this->params[$text])) {
            $text = $this->params[$text];
        }

        $session = $this->getSession();
        $page = $session->getPage();
        $foundRegion = $page->find('region', $region);

        if(is_null($foundRegion)) {
            throw new Exception("Region: " . $region . " not found");
        }

        $element = $foundRegion->find('xpath', $session->getSelectorsHandler()->selectorToXpath(
            'xpath', '//*[contains(text(),"' . $text . '")]')
        );

        if(!isset($element)) {
            throw new Exception('Element containing '. $text . ' cannot be found!');
        }
    }

    /**
     * Click on text in specified region
     *
     * @When I click on the text :text in the :region region
     * @param $text
     * @param $region
     * @throws Exception When region or element cannot be found
     */
    public function clickOnTextInRegion($text, $region) {
        $session = $this->getSession();
        $page = $session->getPage();
        $foundRegion = $page->find('region', $region);

        if(is_null($foundRegion)) {
            throw new Exception("Region: " . $region . " not found");
        }

        $element = $foundRegion->find('xpath', $session->getSelectorsHandler()->selectorToXpath(
            'xpath','//*[contains(text(),"' . $text . '")]')
        );

        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

    /**
     * Click on element in specified region
     *
     * @When I click on the element :cssSelector in the :region region
     * @param $cssSelector
     * @param $region
     * @throws Exception When region or element cannot be found
     */
    public function clickOnElementInRegion($cssSelector, $region) {

        $session = $this->getSession();
        $page = $session->getPage();
        $foundRegion = $page->find('region', $region);
        if(is_null($foundRegion)) {
            throw new Exception("Region: " . $region . " not found");
        }

        $element = $foundRegion->find('css', $cssSelector);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $element->click();

    }

  /**
  * Return a region from the current page.
  *
  * @throws Exception
  *   If region cannot be found.
  *
  * @param string $region
  *   The machine name of the region to return.
  *
  * @return NodeElement
  */
  public function getRegion($region) {

    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);

    if (!$regionObj) {
      throw new Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }

    return $regionObj;
  }


    /**
     * Should see text in certain element in specified region
     *
     * @Then /^I should see "([^"]*)" in the "([^"]*)" element in the "([^"]*)" region$/
     * @param $text
     * @param $tag
     * @param $region
     * @throws Exception
     */
  public function assertRegionElementText($text, $tag, $region) {

    $regionObj = $this->getRegion($region);
    $results = $regionObj->findAll('css', $tag);

    if (!empty($results)) {
      foreach ($results as $result) {
        if ($result->getText() == $text) {
          return;
        }
      }
    }

    throw new Exception(sprintf('The text "%s" was not found in the "%s" element in the "%s" region on the page %s', $text, $tag, $region, $this->getSession()->getCurrentUrl()));
  }

    /**
     * Store attribute of the first element in the first row of table
     *
     * @When I store the :attribute of the first :element in the first row of :tableId table to :variable variable
     * @param $attr
     * @param $element
     * @param $tableId
     * @param $variable
     * @throws Exception
     */
    public function iStoreAttrOfElementToVariable($attr, $element, $tableId, $variable)
    {

        $attr_value = $this->getSession()->getPage()->find('css',$tableId)
            ->find('css','tbody')->find('css','tr')
            ->find('css','td')->find('css',$element)
            ->getAttribute($attr);

        if(!is_null($attr_value)) {
            $this->iStoreVariableWithValueToTheJsonFile($variable,$attr_value);
        } else {
            throw new Exception("Element not found");
        }
    }

    /**
     * @Then /^I store the "([^"]*)" attribute of element with jQuery selector "([^"]*)" in "([^"]*)" variable$/
     * @param string $name
     * @param string $selector
     * @param string $variable
     * @throws Exception
     */
    public function iStoreTheAttributeOfElementWithJQuerySelectorInVariable($name, $selector, $variable)
    {
        $script = "return jQuery('$selector').attr('$name');";
        $value = $this->getSession()->evaluateScript($script);
        if(!is_null($value)) {
            $this->iStoreVariableWithValueToTheJsonFile($variable, $value);
        } else {
            throw new Exception("Value of attribute $name from element $selector cannot be extracted");
        }
    }

    /**
     * Should not see text in ceratin element in specified region
     *
     * @Then /^I should not see "([^"]*)" in the "([^"]*)" element in the "([^"]*)" region$/
     * @param $text
     * @param $tag
     * @param $region
     * @throws Exception
     */
  public function assertNotRegionElementText($text, $tag, $region) {

    $regionObj = $this->getRegion($region);
    $results = $regionObj->findAll('css', $tag);

    if (!empty($results)) {
      foreach ($results as $result) {
        if ($result->getText() == $text) {
          throw new Exception(sprintf('The text "%s" was found in the "%s" element in the "%s" region on the page %s', $text, $tag, $region, $this->getSession()->getCurrentUrl()));
        }
      }
    }
  }

    /**
     * Should see image alt in specified region
     *
     * @Then /^I should see the image alt "(?P<link>[^"]*)" in the "(?P<region>[^"]*)"(?:| region)$/
     * @param $alt
     * @param $region
     * @throws Exception
     */
  public function assertAltRegion($alt, $region) {

    $regionObj = $this->getRegion($region);
    $elements = $regionObj->findAll('css', 'img');

        foreach ($elements as $element) {
            $tmp = $element->getAttribute('alt');

            if ($alt == $tmp) {
                $result = $alt;
            }
        }

    if (empty($result)) {
      throw new Exception(sprintf('No alt text matching "%s" in the "%s" region on the page %s', $alt, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
  * Wait until the Panels IPE is activated.
  *
  * @When /^(?:|I )wait for the Panels IPE to activate$/
  */
  public function waitForIPEtoActivate() {

    $this->getSession()->wait(5000, 'jQuery(".panels-ipe-editing").length > 0');
  }

  /**
  * Wait until the Panels IPE is deactivated.
  *
  * @When /^(?:|I )wait for the Panels IPE to deactivate$/
  */
  public function waitForIPEtoDeactivate() {

    $this->getSession()->wait(5000, 'jQuery(".panels-ipe-editing").length === 0');
  }

  /**
  * Enable the Panels IPE if it's available on the current page.
  *
  * @When /^(?:|I )customize this page with the Panels IPE$/
   * @throws ElementNotFoundException
  */
  public function customizeThisPageIPE() {

    $this->getSession()->getPage()->clickLink('Customize this page');
    $this->waitForIPEtoActivate();
  }

  /**
  * Waiting for suggestion box to appear
  *
  * @Then /^I wait for the suggestion box to appear$/
  */
  public function iWaitForTheSuggestionBoxToAppear() {

    $this->getSession()->wait(5000, "$('#autocomplete').children().length > 0");
  }

    /**
     * Should see specified minimum records
     *
     * @Given /^I should see at least "([^"]*)" records$/
     * @param $count
     * @throws Exception
     */
  public function iShouldSeeAtLeastRecords($count) {

    $element = $this->getSession()->getPage();
    // counts the number of rows in the view table
    $records = $this->getViewDisplayRows($element);
    if ($records == "" || sizeof($records) < $count) {
      throw new Exception("The page (" . $this->getSession()->getCurrentUrl() .
         ") has less than " . $count . " records");
    }
  }

    /**
     * Should see certain column sorted in ascending/descending order
     *
     * @Then /^I should see "([^"]*)" sorted in "([^"]*)" order$/
     * @param $column
     * @param $order
     * @return bool
     * @throws Exception
     */
  public function iShouldSeeSortedInOrder($column, $order) {

    $column_class = "";
    $count = 0;
    $date = FALSE;
    $page = $this->getSession()->getPage();
    $heading = $page->findAll('css', '.view table.views-table th');

    foreach ($heading as $text) {
      if ($text->getText() == $column) {
        $count = 1;
        $class = $text->getAttribute("class");
        $temp = explode(" ", $class);
        $column_class = $temp[1];
        break;
      }
    }

    if ($count == 0) {
      throw new Exception("The page does not have a table with column '" . $column . "'");
    }

    $count = 0;
    $items = $page->findAll('css', '.view table.views-table tr td.'.$column_class);

    // make sure we have the data
    if (sizeof($items)) {
      // put all items in an array
      $loop = 1;
      //date_default_timezone_set ("UTC");
      foreach ($items as $item) {
        $text = $item->getText();

        if ($loop == 1) {
          // check if the text is date field
          if ($this->isStringDate($text)) {
            $date = TRUE;
          }
        }

        if ($date) {
          $orig_arr[] = $this->isStringDate($text);
        }
        else {
          $orig_arr[] = $text;
        }

        $loop = 2;
      }
      // create a temp array for sorting and comparing
      if(isset($orig_arr)) {
          $temp_arr = $orig_arr;
      }
      // sort
      if ($order == "ascending") {
        if ($date) {
          rsort($temp_arr, SORT_NUMERIC);
        }
        else {
          rsort($temp_arr);
        }
      }
      elseif ($order == "descending") {
        if ($date) {
          sort($temp_arr, SORT_NUMERIC);
        }
        else {
          sort($temp_arr);
        }
      }
      // after sorting, compare each index value of temp array & original array
      for ($i = 0; $i < sizeof($temp_arr); $i++) {
        if ($temp_arr[$i] == $orig_arr[$i]) {
          $count++;
        }
      }
      // if all indexs match, then count will be same as array size
      if ($count == sizeof($temp_arr)) {
        return true;
      }
      else {
        throw new Exception("The column '" . $column . "' is not sorted in " . $order . " order");
      }
    }
    else {
      throw new Exception("The column '" . $column . "' is not sorted in " . $order . " order");
    }
  }

//--------------------Bozhidar's FUNCTIONS without step definitions---------------------


    /**
     * @Given I wait AJAX to finish for :seconds seconds
     * @Given I wait for AJAX to finish
     * @Given I wait for AJAX loading to finish
     * @param int $seconds
     */
    public function iWaitiForAjaxToFinishSeconds($seconds = 10) {
        $seconds = $seconds * 1000;
        $this->getSession()->wait($seconds, '(0 === jQuery.active)');
    }

  /**
  * Function to get the array of records from the current view listing
  * @param $page Object The page object to look into
  * @return array $results
  */
  private function getViewDisplayRows($page) {

    $result = array();
    $classes = array(
      'table' => '.view table.views-table tr',
      'grid' => '.view table.views-view-grid tr td',
      'row' => '.view div.views-row'
    );

    foreach ($classes as $type => $class) {
      $result = $page->findAll('css', $class);

      if (!empty($result)) {
        break;
      }
    }

    return $result;
  }

  /**
  * Function to check whether the given string is a date or not
  * @param $string String The string to be checked for
  * @return mixed
  */
  public function isStringDate($string) {

      if (isset($string)) {
          $string = trim($string);
          $time = strtotime($string);
          if(is_int($time)) {
              return $time;
          } else {
              return false;
          }
      }
      return false;
  }

  /**
   * Log in Drupal with provided username and password.
   *
   * @Given I log in
   * @throws Exception
   */
  public function iLogIn() {

    $session = $this->getSession();
    $page = $session->getPage();
    $usern = $this->params["username"];
    $passw = $this->params["password"];
    try {
    $page->fillField("edit-name", $usern);
    $page->fillField("edit-pass", $passw);
    $page->pressButton("edit-submit");

    } catch (ElementNotFoundException $e) {
        throw new Exception($e->getMessage());

    }
  }



    /**
     * Click on the element with the provided xpath query
     *
     * @When /^(?:|I )click on the element "([^"]*)"$/
     * @param $locator
     * @throws Exception
     */
    public function iClickOnTheElement($locator) {
        $session=$this->getSession();
        $page=$session->getPage();
        $element = $page->find('css', $locator); // runs the actual query and returns the element

        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

  /**
  * Reload the current page without GET parameters
  *
  * @When I reload the current page without GET parameters
  */
  public function iReloadTheCurrentPageWithoutGetParameters() {

    $hacker = $this->getSession()->getCurrentUrl();
    $hackers = explode ("?destination", $hacker);
    $this->getSession()->visit($hackers[0]);
  }



    /**
     * Click on the div with the provided css locator
     *
     * @When /^(?:|I )click on the div "([^"]*)"$/
     * @param $locator
     * @throws Exception
     */
    public function iClickOnTheDiv($locator)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find('css', 'div.' . $locator);

        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

    /**
     * Click on the div with the provided title
     *
     * @When /^(?:|I )click on the div with title "([^"]*)"$/
     * @param $title
     * @throws Exception
     */
    public function iClickOnTheDivWithTitle($title) {

        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find('css', 'div[title="' . $title . '"]');
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();

    }

    /**
     * Click on the <a> with the provided css locator. Add '#'' for ids and '.' for classes
     *
     * @When /^(?:|I )click on the a "([^"]*)"$/
     * @param $locator
     * @throws Exception
     */
    public function iClickOnTheLink($locator) {

        $session = $this->getSession();
        $element = $session->getPage()->find('css', 'a' . $locator);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

    /**
     * Click on the <a> with the provided href
     *
     * @When I click on the a with href :href
     * @When I click on the a with href :href from JSON
     * @param $href
     * @throws Exception
     */
    public function iClickOnTheLinkWithHref($href) {
        if (!is_null($this->iGetTheVariableFroMJsonFile2($href))){
            $href = $this->iGetTheVariableFroMJsonFile2($href);
        }
        $session = $this->getSession();
        $element = $session->getPage()->find('css', 'a[href="' . $href . '"]');
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

    /**
     * Click on the <a> with the provided word that exist in href
     *
     * @When I click on the a with href containing :word
     * @param $word
     * @throws Exception
     */
    public function iClickOnTheLinkwithHrefContaining($word) {
        $session = $this->getSession();
        $element = $session->getPage()->find('xpath', '//a[contains(@href,' . $word . ')]');
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $element->click();
    }

    /**
     * Click on the <a> with the provided target
     *
     * @When I click on the a with target :target
     * @param $target
     * @throws Exception
     */
      public function iClickOnTheLinkwithTarget($target) {

          $session = $this->getSession(); // get the mink session
          $element = $session->getPage()->find('css', 'a[target="' . $target . '"]');
          if(is_null($element)) {
              throw new Exception("Element not found");
          }

          $element->click();
      }

    /**
     * Use this for time format "HH:MM"
     *
     * @Given /^I click on time in "([^"]*)" region, from dropdown that is "([^"]*)" hours from now$/
     * @param $region
     * @param $value
     * @throws Exception
     */
      public function iClickOnTimeWithSpecificFormat($region,$value) {
          $current_time = date("G", time());
          $hours_from_now = $current_time + $value;
          $hours_from_now = (string)$hours_from_now . ':00';
          $session = $this->getSession();
          $element = $session->getPage()->find(
              'region', $region)->find(
                  'xpath', $session->getSelectorsHandler()->selectorToXpath(
                      'xpath', '//*[contains(text(),"' . $hours_from_now . '")]')
          );
          if(is_null($element)) {
              throw new Exception("Element not found");
          }

          $element->click();

      }


    /**
     * Save data from provided css locator
     *
     * @When /^I save data from css "([^"]*)" to variable "([^"]*)" in JSON$/
     * @param $locator
     * @param $variable
     * @throws Exception
     */
      public function iSaveDataFromCss($locator, $variable) {

          $session = $this->getSession(); // get the mink session
          $element = $session->getPage()->find('css', $locator);
          if(is_null($element)) {
              throw new Exception("Element not found");
          }

          $text = $element->getText();
          $this->iStoreVariableWithValueToTheJsonFile($variable, $text);
      }

      /**
      * Save page's url
      *
      * @When /^I save current URL to variable "([^"]*)" in JSON$/
      * @param $variableName
      */
      public function iSaveUrl($variableName) {
          $currentURL = $this->getSession()->getCurrentUrl();
          $this->iStoreVariableWithValueToTheJsonFile($variableName, $currentURL);
      }

      /**
      * Visit saved URL
      *
      * @When I go to saved :variableName URL
      * @param $variableName
      * @throws Exception
      */
      public function iGoToSavedUrl($variableName) {
           $savedURL = $this->iGetTheVariableFroMJsonFile($variableName);
           $this->getSession()->visit($savedURL);
      }

    /**
     * Save data from provided xpath to variable in JSON
     *
     * @When I save data from xpath :xpath to variable :variableName in JSON
     * @param $xpath
     * @param $variableName
     * @throws Exception
     */
    public function iSaveDataFromXpath($xpath, $variableName) {

        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find(
            'xpath', $session->getSelectorsHandler()->selectorToXpath(
                'xpath', $xpath)
        );
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $saved_data = $element->getText();

        $this->iStoreVariableWithValueToTheJsonFile($variableName, $saved_data);
  }



    /**
     * This gets the window name of the new popup.
     *
     * @return mixed
     * @throws Exception
     */
      private function getNewPopup() {
        try {
            $originalWindowName = $this->getSession()->getDriver()->getWindowName();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        //Get all of the window names first
        $names = $this->getSession()->getWindowNames();
        //Now it should be the last window name
        $last = array_pop($names);

        if (!empty($originalWindowName)) {
          while ($last == $originalWindowName && !empty($names)) {
            $last = array_pop($names);
          }
        }

        return $last;
      }

      /**
      * Switching the perspective to the popup
      *
      * @Then I switch to popup
      * @throws Exception
      */
      public function iSwitchToPopup() {
          try {
              $originalWindowName = $this->getSession()->getDriver()->getWindowName();
              if (empty($this->originalWindowName)) {
                  $this->originalWindowName = $originalWindowName;
              }
              $popupName = $this->getNewPopup();
              //Switch to the popup Window
              $this->getSession()->switchToWindow($popupName);
          } catch (Exception $e) {
              throw new Exception($e->getMessage());
          }
      }

      /**
       * This function check for error messages.
       *
       * @When I check for errors and warnings
       * @throws Exception
       */
      public function errorCheck() {
          $session = $this->getSession();
          $page = $session->getPage();
          $current_page = $session->getCurrentUrl();
          $errors = $page->findAll('css','.error');
          $warnings = $page->findAll('css', '.messages-warning');
          $error_messages = "";
          $warning_messages = "";
          if (isset($errors)) {
              foreach($errors as $error) {
                  $error_messages = $error_messages." ".$error->getText();
              }
          }
          if (isset($warnings)) {
              foreach($warnings as $warning) {
                  $warning_messages = $warning_messages." ".$warning->getText();
              }
          }
          if (isset($errors) && isset ($warnings)) {
              throw new Exception(sprintf(
                  "The following error(s) and warning(s) were found on the %s page:\n %s \n %s", $current_page,$error_messages,$warning_messages));
          }
          else if(isset($errors)) {
              throw new Exception(sprintf(
                  "The following error(s) were found on the %s page:\n %s", $current_page,$error_messages));
          }
          else if(isset($errors)) {
              throw new Exception(sprintf(
                  "The following warning(s) were found on the %s page:\n %s", $current_page,$warning_messages));
          }
      }

    /**
     * Assert value for certain input, text area fields.
     *
     * @When /^I should see "([^"]*)" in "([^"]*)"$/
     * @param $value
     * @param $field
     * @return bool
     * @throws Exception
     */
  public function assertValueInField($value, $field) {

    $session = $this->getSession();
    $element = $session->getPage()->find('css', "#".$field);
    if ($element == NULL) {
        $element = $session->getPage()->find('css', ".".$field);
    }
    // Get the element tag name
    if (empty($element)) {
        throw new Exception("The element is NULL. Are you sure you are providing proper id/class?");
    }
    $element_tag_name = $element->getTagName();
    switch($element_tag_name) {
        case "input":
            if (isset($element)) {
                $text = $element->getValue();
            } else {
                throw new Exception(sprintf("Element is null"));
            }
            if ($text === $value) {
                return true;
            } else {
                throw new Exception(sprintf('Value of input : "%s" does not match the text "%s"', $text, $value));
            }
            break;
        default:
            if (isset($element)) {
                $text = $element->getText();
            } else {
                throw new Exception(sprintf("Element is null"));
            }
            if ($text === $value) {
                return true;
            } else {
                throw new Exception(sprintf('Text of field : "%s" does not match the text "%s"', $text, $value));
            }
            break;
    }

  }

    /**
     * Assert value for certain input
     *
     * @When /^I should not see value "([^"]*)" in input "([^"]*)"$/
     * @param $value
     * @param $cssSelector
     * @return bool
     * @throws Exception
     */
    public function assertValueInInputNotSee($value, $cssSelector) {

        $session = $this->getSession();
        $element = $session->getPage()->find('css', $cssSelector);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $text = $element->getValue();
        if($text == $value) {
            throw new Exception(sprintf('Value of input : "%s" matches the text "%s"', $text, $value));
        }
        return true;
    }

    /**
     * Assert that text exist only once on page
     *
     * @When /^I should see the text "([^"]*)" once$/
     * @param $text
     * @throws Exception
     */
      public function assertOnlyOnce($text) {
          if(!is_null($this->iGetTheVariableFroMJsonFile2($text))){
              $text = $this->iGetTheVariableFroMJsonFile2($text);
          }
          $session = $this->getSession();
          $elements = $session->getPage()->findAll(
              'xpath', $session->getSelectorsHandler()->selectorToXpath(
                  'xpath', '//*[contains(text(),"'. $text .'")]')
          );
          if(count($elements) > 1) {
              throw new Exception(sprintf("The text is found more than once"));
          }
          else if (count($elements) == 0) {
              throw new Exception(sprintf("The text is missing"));
          }
      }

    /**
     * Step override from Mink context to handle stored variables
     *
     * @param $text
     * @throws Exception
     */
    public function assertPageContainsText($text)
    {
//        if(!is_null($this->iGetTheVariableFroMJsonFile2($text))){
//            $text = $this->iGetTheVariableFroMJsonFile2($text);
//        }
        parent::assertPageContainsText($text);
    }

  /**
   * Select all checkboxes
   *
   * @When I select all
   * @When I select all results
   */
  public function SelectAll() {
    $session = $this->getSession();
    $page = $session->getPage();
    $checkboxes = $page->findAll('css','.vbo-table-select-all');
    if (isset($checkboxes[0])) {
        #$this->getSession()->getPage()->checkField($checkboxes[0]);
        $checkboxes[0]->click();
    }
  }


    /**
     * Click on the text which is value of input in region
     *
     * @When /^I click on the text "([^"]*)" in input in region "([^"]*)"$/
     * @param $value
     * @param $region
     * @throws Exception
     */
    public function clickValueInInputofRegion($value,$region) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
        'region', $region)->find(
            'xpath', $session->getSelectorsHandler()->selectorToXpath(
                'xpath', '//input[@value="' . $value . '"]'));
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

    /**
     * Click on the text which is value of input
     *
     * @When /^I click on the text "([^"]*)" in input$/
     * @param $value
     * @throws Exception
     */
    public function clickValueInInput($value) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
        'xpath', $session->getSelectorsHandler()->selectorToXpath(
            'xpath','//input[@value="' . $value . '"]')
        );
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();
    }

    /**
     * Assert that option exist in a select
     *
     * @When I assert that select :select has option :option
     * @param $select
     * @param $option
     * @throws Exception
     * @return bool
     */
    public function assertSelectHasOption($select,$option) {
        $session = $this->getSession();
        $element = $session->getPage()->find('css',$select);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $options = $element->findAll('css','option');
        foreach($options as $opt){
            if($opt->getText()==$option){
                return true;
            }
        }
        throw new Exception(sprintf("The option %s is not presented in the select %s",$option,$select));
    }



// ---------------BBD-------------------

    /**
     * Select nth option from a dropdown. The count starts from a 0. Works with #id .class
     * Example: And I select "2" option in a row from the "#edit-field-editorial-body-0-layout" dropdown
     *
     * @When I select :value option in a row from the :cssSelector dropdown
     * @param $cssSelector
     * @param $value
     * @throws Exception
     */
    public function selectFromDropdownByIndex($cssSelector, $value) {
        $session = $this->getSession();
        $page = $session->getPage();
        $dropdown = $page->find('css',$cssSelector);
        if(is_null($dropdown)) {
            throw new Exception("Select not found");
        }
        $options = $dropdown->findAll('css', 'option');
        if (count($options)<$value) {
            throw new Exception('There are less options then requested');
        }
        foreach ($options as $option) {
            $optionsText[] = $option -> getText();
        }
        try {
            $this->selectOption(substr($cssSelector,1),$optionsText[$value]);
        } catch (Exception $e) {
            $e->getMessage();
            throw new Exception('Something went wrong');
        }

    }

    /**
     * Remove the element with the provided CSS Selector using JS
     *
     * @When /^I remove the element with css selector "((?:[^"]|\")*)" using JS$/
     * @param $cssSelector
     * @throws Exception
     */
    public function iRemoveTheElementWithCSSSelectorUsingJS($cssSelector) {

        $javascript = "jQuery('$cssSelector').remove()";

        try {
            $this->getSession()->executeScript($javascript);
        } catch (Exception $e) {
            $e->getMessage();
            throw new Exception('Element not found');
        }
    }

    /**
     * @When I set :name name to an :cssSelector iframe
     * @throws Exception
     */
    public function setNametoAnIframe($cssSelector, $name) {
        $session = $this->getSession();
        $session->executeScript("jQuery('$cssSelector iframe').attr('name', '$name')");
    }

    /**
     * @When /^I set "((?:[^"]|\")*)" id to the element "((?:[^"]|\")*)"$/
     *
     */
    public function setIDtoAnIframe ($id,$cssSelector) {
        $session = $this->getSession();
        $session->executeScript("jQuery('$cssSelector').attr('id', '$id')");
    }


    /**
     * Click on the span with given text
     *
     * @When /^I click on the span with text "([^"]*)"$/
     * @When /^I click on the span with text "([^"]*)" if visible$/
     * @param $text
     * @throws Exception
     */
    public function iClickOnTheSpanWithText($text){
        $session = $this->getSession();
        $element = $session->getPage()->find('xpath', '//span[contains(text(),"' . $text . '")]');
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $element->click();

    }


    /**
     * Click on the label with the provided text
     *
     * @When /^(?:|I )click on the label "([^"]*)"$/
     * @param string $text
     * @return bool
     * @throws Exception
     */
  public function iClickOnTheLabel($text) {

    $session = $this->getSession();
    $labels = $session->getPage()->findAll('css', 'label');
    foreach ($labels as $label)
    {
      if($label->getText() == $text)
      {
        $label->click();
        return true;
      }
    }
    throw new Exception(sprintf('Label with "%s" text not found', $text));
  }

    /**
     * Double Click on some text.
     *
     * @When /^I doubleclick on the text "([^"]*)"$/
     * @param $text
     * @throws Exception
     */
    public function iDoubleClickOnTheText($text) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath', $session->getSelectorsHandler()->selectorToXpath(
                'xpath', '//*[contains(text(),"' . $text . '")]')
        );
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $element->doubleClick();
    }

    /**
     * Check if a value matches part of the value of certain input
     *
     * @When /^I should see part of the value "([^"]*)" in input "([^"]*)"$/
     * @param $value
     * @param $cssSelector
     * @return bool
     * @throws Exception
     */
    public function checkPartOfTheValueInInput($value, $cssSelector) {
        $session = $this->getSession();
        $element = $session->getPage()->find('css', $cssSelector);
        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $text = $element->getValue();

        if(!strpos($text, $value)) {
            throw new Exception(sprintf(
                'Value of input : "%s" does not partially match the text "%s"', $text, $value));
        }
        return true;
    }

    /**
     * Generate random e-mail extension for google/outlook mail
     *
     * @When I generate extension with :number chars for :mail mail and store it in :variable variable
     * @param int $number
     * @param string $mail
     * @param string $variable
     * @throws Exception
     */
    public function generateRandomMailExtension($number, $mail, $variable) {
        $parts = explode('@', $mail);
        $user = $parts[0];

        $randomExtension = $this->randomString($number);

        // Construct the mail address
        $generatedMail = $user . '+' . $randomExtension . '@' . $parts[1];

        echo $generatedMail; //Added for easier debugging

        //Store the generated mail
        $this->iStoreVariableWithValueToTheJsonFile($variable, $generatedMail);
    }

    /**
     * Asserts that an element with provided selector is not present
     *
     * @When /^I should not see the element with selector "((?:[^"]|\")*)"$/
     * @param $cssSelector
     * @return bool
     * @throws Exception
     */
    public function iShouldNotSeeElementSelector($cssSelector){
        $session = $this->getSession();
        $page = $session->getPage();
        $element =$page->find('css', $cssSelector);
        if(!is_null($element)) {
            throw new Exception("Element with css selector: " . $cssSelector . " exists");
        }
        return true;
    }

    /**
     * Searches for a text in an element with unique css selector
     *
     * @When I should see :text in the element :cssSelector
     * @param $text
     * @param $cssSelector
     * @return bool
     * @throws Exception
     */
    public function iShouldSeeTheTextInTheElement($text, $cssSelector){
        $session = $this->getSession();
        $page = $session->getPage();
        $pageElement = $page->find('css', $cssSelector);
        if(is_null($pageElement)) {
            throw new Exception("Element not found");
        }

        $comparisonText = $pageElement->getText();
        if($text != $comparisonText){
            $exceptionMessage = 'The text being searched: ' . $text . ' does not match the text: ' . $comparisonText . ' of the element';
            throw new Exception($exceptionMessage);
        }
        return true;
    }

    /**
     * Asserts that an element with provided selector is not present
     *
     * @When /^I should see the element with selector "((?:[^"]|\")*)"$/
     * @param $element
     * @return bool
     * @throws Exception
     */
    public function iShouldSeeElementSelector($element){
        $session = $this->getSession();
        $page = $session->getPage();
        $pageElement = $page ->find('css', $element);
        if($pageElement == null){
            throw new Exception("The selector was not found");
        }
        else{
            return true;
        }
    }


    /** Stop the browser session
  * @Then /^I stop the session/
  * @Then /^I end the session/
  */
  public function stopTheSession() {
    $session = $this->getSession();
    $session->stop();
  }

    /** Reset the browser session
     * @Then /^I reset the session/
     * @Then /^I restart the session/
     */
    public function resetTheSession() {
        $session = $this->getSession();
        $session->reset();
    }

    /**
     * I check certain checkbox's id if it is unchecked
     *
     * @When /^I check "([^"]*)" if not checked yet$/
     * @param $id
     * @throws Exception
     */
  public function iCheckIfNotCheckedYet($id) {
    $page = $this->getSession()->getPage();
    $isChecked = $page->findField($id);
    $isChecked = $isChecked->hasAttribute("checked");
    if (!$isChecked) {
      $page->checkField($id);
    }
    else {
      throw new Exception (sprintf('The field %s is already checked.', $id));
    }
  }

    /**
     * I uncheck certain checkbox's id if it's checked
     *
     * @When /^I uncheck "([^"]*)" if checked already$/
     * @param $id
     * @throws Exception
     */
  public function iUncheckIfAlreadyChecked($id) {
    $page = $this->getSession()->getPage();
    $isChecked = $page->findField($id);
    $isChecked = $isChecked->hasAttribute("checked");
    if ($isChecked) {
      $page->uncheckField($id);
    }
    else {
      throw new Exception (sprintf('The field %s is already unchecked.', $id));
    }
  }

// --------------BBS--------------------

    /**
     * Waiting for text to appear in a region with a certain max execution time.
     *
     * @When /I wait for text "([^"]*)" in the "([^"]*)" region to appear with max time "([^"]+)"(?: seconds)?/
     * @param $text
     * @param $region
     * @param $maxExecutionTime
     * @throws Exception
     */
    public function iWaitForTextInTheRegionToAppearWithMaxTime($text, $region, $maxExecutionTime) {;
        $isTextFound = false;
        for ($i = 0; $i < $maxExecutionTime; $i++) {
            try {
                $this->assertRegionText2($text, $region);
                $isTextFound = true;
                break;
            }
            catch (Exception $e) {
                sleep(1);
            }
        }

        if (!$isTextFound) {
            throw new Exception("'$text' didn't appear in the \"'$region' region for $maxExecutionTime seconds");
        }
    }


    /**
     * Click on some text.
     *
     * @When I click on Nth :number appearance of the text :text
     * @param $text
     * @param $number
     * @throws InvalidArgumentException
     */
    public function iClickOnTheNthAppearanceOfTheText($text, $number) {

      $session = $this->getSession();
      $elements = $session->getPage()->findAll(
          'xpath', $session->getSelectorsHandler()->selectorToXpath(
                      'xpath', '//*[contains(text(),"' . $text . '")]')
      );

      if (null === $elements) {
        throw new InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
      }
      if(count($elements)<$number)  {
          throw new InvalidArgumentException(sprintf('Cannot find text this many times: "%s"', $number));
      }
      $elements[$number]->click();
    }


    /**
     * Wait for specific element with .class or #id to appear within X seconds.
     *
     * @Then I wait for element with :elementIdentificator selector to appear
     * @Then I wait for element with :elementIdentificator selector to appear with :maxTime seconds maxtime
     * @param $elementIdentificator
     * @param $maxTime
     * @throws Exception
     */
  public function waitForElementWithSelector($elementIdentificator, $maxTime = 20)
  {
      $lamp = 0;
      for($i=0; $i<=$maxTime; $i++) {
          if ($this->getSession()->getPage()->find("css", $elementIdentificator)==NULL) {
              sleep(1);
          }
          else
          {
              $lamp=1;
              break;
          }
      }
      if(!$lamp) {
          throw new Exception (sprintf('There is no element with identificator %s', $elementIdentificator));
      }

  }




    /**
     * Click on the Nth LI in an UL
     *
     * @Then I click on the Nth :number LI in the UL with selector :selector
     * @param $number
     * @param $selector
     * @throws Exception
     */
  public function clickSpecificLiInUL($number, $selector)
  {
    $ul = $this->getSession()->getPage()->find('css', $selector);
    if (null === $ul){
      throw new InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $selector));
    }
    $element = $ul->findAll('css','li')[$number];
    if (null === $element){
      throw new InvalidArgumentException(sprintf('Could not find the li with number : "%s"', $number));
    }
    $element->click();
  }

    /**
     * iOpenANewWindowUrl
     *
     * @When I open new tab with :url url
     *
     * @param   string  $url
     * @throws Exception
     */
    public function iOpenANewWindowUrl($url)
    {
        $session = $this->getSession();
        $driver = $session->getDriver();

        $originalWindowName = $session->getDriver()->getWindowName(); //Get the original name

        if (empty($this->originalWindowName)) {
            $this->originalWindowName = $originalWindowName;
        }


        try {
            $driver->executeScript("window.open('" . $url . "','_blank');");
            $this->iSwitchToTheWindow(count($driver->getWindowNames()), true);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $nb
     * @param bool $internalFunction
     * @throws DriverException
     * @throws Exception
     */

    private function iSwitchToTheWindow($nb, $internalFunction = false)
    {
        $driver = $this->getSession()->getDriver();

        $windowNames = $driver->getWindowNames();
        if (!$internalFunction && ( $nb < 0 || $nb >= count($windowNames) ) )
        {
            throw new Exception(
                sprintf(
                    "Error : index of window out of bounds, given '%b' but number of windows is '%b'",
                    $nb,
                    count($windowNames)
                )
            );
        }
        $driver->switchToWindow($windowNames[$nb-1]);
    }

    /**
     * Storing isolated string based on pattern in text
     *
     * @When I store the string matching the :pattern pattern from the :text text
     *
     * @param   string  $text
     * @param $pattern
     * @throws Exception
     */
    public function iStoreStringInTextByPattern ($pattern, $text) {

        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath(
                'xpath', '//*[contains(text(),"' . $text . '")]'));
        $textFound = $element->getText();

        preg_match($pattern, $textFound, $matches);

        $string = $matches[0];

        $this->iStoreVariableWithValueToTheJsonFile('storedString', $string);

    }


    /**
    * Switching to the newly popuped window
    *
    * @Then /^I switch to other window$/
    * @throws Exception
    */
    public function iSwitchToOtherWindow() {
        $originalWindowName = $this->getSession()->getDriver()->getWindowName(); //Get the original name

        if (empty($this->originalWindowName)) {
            $this->originalWindowName = $originalWindowName;
        }
        $this->getSession()->getPage();
        $popupName = $this->getNewPopup();
        //Switch to the new Window
        $this->getSession()->switchToWindow($popupName);
    }

    /**
     * Closing the current window
     *
     * @Then /^I close window$/
     */
    public function iCloseWindow() {
        $this->getSession()->stop();
    }

    /** Fill in selected field the random generated string.
     *
     * @Given /^I fill in "([^"]*)" with random string$/
     * @param $field
     * @throws ElementNotFoundException
     */
    public function iFillInWithRandomString($field)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $random_string = $this->randomString(5);
        $page->fillField($field, $random_string);
    }


    /**
    * Function for clearing the cache and returning to the front page
    *
    * @Given /^I clear cache$/
    * @throws ElementNotFoundException
    */
    public function clearCache() {
        $this->getSession()->visit($this->locatePath('/admin/config/development/performance'));
        sleep(1);
        $this->getSession()->getPage()->pressButton('edit-clear');
        sleep(1);
        $this->getSession()->visit($this->locatePath('/'));
    }

    /**
     * The iframe in certain element has specified id
     *
     * @Then the :number iframe in element :arg1 has id :arg2
     * @param $number
     * @param $element_id
     * @param $iframe_id
     * @throws Exception
     */
  public function theIframeInElementHasId($element_id, $iframe_id, $number = 1) {
    $function = "
      var elem = document.getElementById('$element_id');
      console.log(elem);
      var iframes = elem.getElementsByTagName('iframe');
      console.log(iframes);
      var f = iframes['$number'-1];
      f.id = '$iframe_id';
      ";

      try {
      $this->getSession()->executeScript($function);
    }
    catch(Exception $e) {
      throw new Exception(sprintf('No iframe found in the element "%s" on the page "%s".', $element_id, $this->getSession()->getCurrentUrl()));
    }
  }

    /**
     * Get text from element
     *
     * @Given /^I write "([^"]+)" into "([^"]+)" wysiwyg$/
     * @param $text
     * @param $iframeId
     * @throws Exception
     */
  public function writeElementText($text,$iframeId) {
    $this->getSession()->wait(500);
    $id = 'behat' . round(microtime(true) * 1000);
    $this->theIframeInElementHasId($iframeId, $id);
    $this->iSwitchToIframe($id);
    $text = json_encode($text);
    $function = "
         var elem = document.getElementsByTagName('*');
         g=elem[0];
         g.innerHTML='$text';
    ";
    $this->getSession()->executeScript($function);
    $this->iSwitchBackToOriginalWindow();
  }

    /**
     * Select option with value stored in JSON
     * @When I select option with value :variable from :select
     *
     * @param $select
     * @param $variable
     * @throws Exception
     */
    public function selectOptionWithValueFromJson($variable, $select) {
        $decoded_json = json_decode(file_get_contents($this->params["json_file_path"]), TRUE);

        if(isset($decoded_json[$variable])) {
            $option = $decoded_json[$variable];
            parent::selectOption($select, $option);
        } else {
            throw new Exception("Variable with name: $variable not found in JSON");
        }

    }

    /**
     * Select option from dropdown by text
     * @When I select option with text value :text from :select
     *
     * @param $text
     * @param $select
     * @throws Exception
     */
    public function selectOptionWithTextValue($text, $select) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',$session->getSelectorsHandler()->selectorToXpath(
            'xpath', '//*[contains(text(),"' . $text . '")]'));

        if (!$element) {
            throw new Exception('Element not found');
        }

        $value = $element->getAttribute('value');

        if(isset($value)) {
            parent::selectOption($select, $value);
        } else {
            throw new Exception("Option does not have value");
        }
    }


    /**
     * Fill chosen fields with javascript
     * Legacy step left for backward compatibility
     *
     * @When I select :option from chosen :selector
     * @param $selector
     * @param $option
     * @throws Exception
     */
    public function iSelectOptionWithJavascript($option, $selector) {
        $page = $this->getSession()->getPage();
        $field = $page->findField($selector);
        if(is_null($field)) {
            throw new Exception("Element not found");
        }
        $cssSelector = '#' . $field->getAttribute('id');
        $this->iSelectFromChosenJsSelectBox($option, $cssSelector);
    }

    /**
     * Selection the first autocomplete option for specified string on certain field
     *
     * @When /^I select the first autocomplete option for "([^"]*)" on the "([^"]*)" field$/
     * @param $string
     * @param $field
     * @throws ElementNotFoundException
     * @throws DriverException
     */
    public function iSelectFirstAutocomplete($string, $field) {

        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->findField($field);

        if (!$element) {
            throw new ElementNotFoundException($session, NULL, 'named', $field);
        }

        $page->fillField($field, $string);
        $xpath = $element->getXpath();
        $driver = $session->getDriver();

        $last_char = substr($string, -1);

        // Remove last char an write it again
        $driver->keyDown($xpath, 8);
        $driver->keyUp($xpath, 8);
        $driver->keyDown($xpath, $last_char);
        $driver->keyUp($xpath, $last_char);

        // Wait for AJAX to finish.
        $this->getSession()->wait(500, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');

        // And wait for 1 second just to be sure.
        sleep(1);

        // Press the down arrow to select the first option.
        $driver->keyDown($xpath, 40);
        $driver->keyUp($xpath, 40);
        // Press the Enter key to confirm selection, copying the value into the field.
        $driver->keyDown($xpath, 13);
        $driver->keyUp($xpath, 13);
        // Wait for AJAX to finish.
        $this->getSession()->wait(500, '(typeof(jQuery) == "undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    }



    /**
     * Selecting option from chosen by valid css selector
     *
     * @When I choose :option from chosen with css selector :cssSelector
     * @param $option
     * @param $cssSelector
     * @throws Exception When the element cannot be found
     */
    public function iSelectFromChosenJsSelectBox($option, $cssSelector) {

        $page = $this->getSession()->getPage();

        $element = $page->find('css', $cssSelector);

        if(is_null($element)) {
            throw new Exception("Element not found");
        }

        $opt = $element->find('named', array('option', $option));

        if(is_null($opt)) {
            throw new Exception("Option: " . $opt . " not found");
        }
        $value = $opt->getValue();

        $javascript = "jQuery('$cssSelector').val('$value');
                  jQuery('$cssSelector').trigger('chosen:updated');
                  jQuery('$cssSelector').trigger('change');";

        $this->getSession()->executeScript($javascript);
    }


    /**
     * Should see image title in specified region
     *
     * @Then /^I should see the image title "(?P<link>[^"]*)" in the "(?P<region>[^"]*)"(?:| region)$/
     * @param $title
     * @param $region
     * @return bool
     * @throws Exception
     */
    public function assertTitleRegion($title, $region) {
        $regionObj = $this->getRegion($region);
        $element = $regionObj->find('css', 'img');
        if(is_null($element)) {
            throw new Exception("Element not found");
        }
        $titleOfTheElement = $element->getAttribute('title');
        if ($title != $titleOfTheElement) {
            throw new Exception(sprintf(
                'No title text matching "%s" in the "%s" region on the page %s',
                $title, $titleOfTheElement, $this->getSession()->getCurrentUrl()));
        }
        return true;
    }

    /**
     * Check if img is loaded/rendered in specified region
     *
     * @Then /^I should see an image in the "(?P<region>[^"]*)"(?:| region)$/
     * @param $region
     * @throws Exception
     */
    public function checkForImage($region)
    {

        $regionObj = $this->getRegion($region);
        $element = $regionObj->find('css', 'img');
        if (is_null($element)) {
            throw new Exception("Element not found");
        }
        $path = $element->getAttribute('src');

        $base_url = $this->getMinkParameter('base_url');

        if (substr($base_url, -1) == '/') {
            // Strip trailing slash when present.
            $base_url = substr($base_url, 0, strlen($base_url) - 1);
        }
        $url = $base_url . parse_url($path, PHP_URL_PATH);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        // get the content type
        $mime_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if (strpos($mime_type, 'image/') === FALSE) {
            throw new Exception (sprintf('%s did not return an image', $url));

        }
    }

    /**
     * Checking for specified text for certain seconds with interval of 1 second
     *
     * @Then I should check for the text :arg1 for :arg2 seconds
     * @param $text
     * @param $sec
     * @throws Exception
     */
  public function checkIfPageContainsTextForTime($text, $sec) {
    $flag = FALSE;
    $session = $this->getSession();
    for ($i = 0; $i < $sec; $i++) {
        $element = $session->getPage()->find('xpath', $session->getSelectorsHandler()->selectorToXpath('xpath', '//*[contains(text(),"' . $text . '")]'));
        if(!isset($element)) {
          sleep(1);
        }
        else {
          $flag = TRUE;
        }
    }
    if($flag == FALSE) {
      throw new Exception(sprintf("Can not find the text after '%s' seconds", $sec));
    }
  }

    /**
     * This step can either work with previously saved variable or with custom value
     * Filling a field with value
     *
     * @When I fill :field field with string :value
     * @param $field
     * @param $value
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function fillFieldWithString($field, $value)
    {
        try {
            $value = $this->iGetTheVariableFroMJsonFile($value);
        } catch (Exception $e) {
            echo 'A variable is not set in the JSON so the value from the step will be used.';
            // Do not break execution when the variable is not set
        }
        $element = $this->getSession()->getPage()->find('css', $field);
        if (null === $element) {
            throw new InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $field));
        }
        $element->setValue($value);
    }

    /**
     * Fills in a form field with id|name|title|alt|value in the specified region.
     *
     * @Given I fill in :value for :field in the :region( region)
     * @Given I fill in :field with :value in the :region( region)
     * @param $field
     * @param $value
     * @param $region
     *
     * @throws Exception
     * @throws ElementNotFoundException
     */
    public function regionFillField($field, $value, $region) {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        $regionObj = $this->getRegion($region);
        $regionObj->fillField($field, $value);
    }

    /**
     * @When I follow/click :link in the :region( region)
     *
     * @param $link
     * @param $region
     * @throws Exception If region or link within it cannot be found.
     */
    public function assertRegionLinkFollow2($link, $region) {
        $regionObj = $this->getRegion($region);

        // Find the link within the region
        $linkObj = $regionObj->findLink($link);
        if (empty($linkObj)) {
            throw new Exception(sprintf('The link "%s" was not found in the region "%s" on the page %s', $link, $region, $this->getSession()->getCurrentUrl()));
        }
        $linkObj->click();
    }
    /**
     * @Given I check the box :checkbox
     * @param $checkbox
     */
  public function assertCheckBox2($checkbox) {
    // Use the Mink Extension step definition.
    $this->checkOption($checkbox);
  }

    /**
     * @Given I uncheck the box :checkbox
     * @param $checkbox
     */
  public function assertUncheckBox2($checkbox) {
    // Use the Mink Extension step definition.
    $this->uncheckOption($checkbox);
  }

    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @When I press the :button button
     * @param $button
     */
  public function pressButton2($button) {
    // Wait for any open autocomplete boxes to finish closing.  They block
    // form-submission if they are still open.
    // Use a step 'I press the "Esc" key in the "LABEL" field' to close
    // autocomplete suggestion boxes with Mink.  "Click" events on the
    // autocomplete suggestion do not work.
    try {
      $this->getSession()->wait(1000, 'typeof(jQuery)=="undefined" || jQuery("#autocomplete").length === 0');
    }
    catch (Exception $e) {
      // The jQuery probably failed because the driver does not support
      // javascript.  That is okay, because if the driver does not support
      // javascript, it does not support autocomplete boxes either.
    }

    // Use the Mink Extension step definition.
    return parent::pressButton($button);
  }

    /**
     * @Then I should see( the text) :text in the :region( region)
     *
     * @param $text
     * @param $region
     * @throws Exception If region or text within it cannot be found.
     */
  public function assertRegionText2($text, $region) {

    $regionObj = $this->getRegion($region);
    // Find the text within the region
    $regionText = $regionObj->getText();
    if (strpos($regionText, $text) === FALSE) {
      throw new Exception(sprintf("The text '%s' was not found in the region '%s' on the page %s", $text, $region, $this->getSession()->getCurrentUrl()));
    }
  }

    /**
     * @Then I should not see( the text) :text in the :region( region)
     *
     * @param $text
     * @param $region
     * @throws Exception If region or text within it cannot be found.
     */
  public function assertNotRegionText2($text, $region) {

    $regionObj = $this->getRegion($region);
    // Find the text within the region.
    $regionText = $regionObj->getText();
    if (strpos($regionText, $text) !== FALSE) {
      throw new Exception(sprintf('The text "%s" was found in the region "%s" on the page %s', $text, $region, $this->getSession()->getCurrentUrl()));
    }
  }

    /**
     * @When I select the radio button :label with the id :id
     * @When I select the radio button :label
     *
     * @TODO convert to mink extension.
     * @param $label
     * @param string $id
     * @throws Exception
     */
    public function assertSelectRadioById($label, $id = '') {
        $element = $this->getSession()->getPage();
        $radiobutton = $id ? $element->findById($id) : $element->find('named', array('radio', $this->getSession()->getSelectorsHandler()->xpathLiteral($label)));
        if ($radiobutton === NULL) {
            throw new Exception(sprintf('The radio button with "%s" was not found on the page %s', $id ? $id : $label, $this->getSession()->getCurrentUrl()));
        }
        $value = $radiobutton->getAttribute('value');
        $labelonpage = $radiobutton->getParent()->getText();
        if ($label != $labelonpage) {
            throw new Exception(sprintf("Button with id '%s' has label '%s' instead of '%s' on the page %s", $id, $labelonpage, $label, $this->getSession()->getCurrentUrl()));
        }
        $radiobutton->selectOption($value, FALSE);
    }

    /**
     * @param $button
     * @param $region
     * @throws ElementNotFoundException
     * @throws Exception
     */
    public function assertRegionPressButton2($button, $region) {
        $regionObj = $this->getRegion($region);
        $buttonObj = $regionObj->findButton($button);
        if (empty($buttonObj)) {
            throw new Exception(sprintf("The button '%s' was not found in the region '%s' on the page %s", $button, $region, $this->getSession()->getCurrentUrl()));
        }
        $regionObj->pressButton($button);
  }


  /**
   * Checks, if a button with id|name|title|alt|value exists or not and press the same
   *
   * @Given I press :button in the :region( region)
   *
   * @param $button
   *   string The id|name|title|alt|value of the button to be pressed
   * @param $region
   *   string The region in which the button should be pressed
   *
   * @throws Exception
   *   If region or button within it cannot be found.
   */
  public function assertRegionPressButton($button, $region) {
      $regionObj = $this->getRegion($region);
      $buttonObj = $regionObj->findButton($button);
      if (empty($buttonObj)) {
          throw new Exception(sprintf("The button '%s' was not found in the region '%s' on the page %s", $button, $region, $this->getSession()->getCurrentUrl()));
      }
      $regionObj->pressButton($button);
  }


    /**
     * @Then I press enter :elem
     * @param $elem
     * @throws Exception
     */
    public function pressEnter($elem){
        $page = $this->getSession()->getPage();
        $element = $page->find('css',$elem);

        $xpath = $element->getXpath();
        $driver = $this->getSession()->getDriver();

        // Press the Enter key to confirm selection, copying the value into the field.
        $driver->keyDown($xpath, 40);
        $driver->keyUp($xpath, 40);
        $driver->keyDown($xpath, 13);
        $driver->keyUp($xpath, 13);
    }

    /**
     * @Then I press down arrow and enter :elem
     * @param $elem
     * @throws Exception
     */
    public function pressEnterAndDownArrow($elem){
        $page = $this->getSession()->getPage();
        $element = $page->find('css',$elem);

        $xpath = $element->getXpath();
        $driver = $this->getSession()->getDriver();

        // Press the Enter key to confirm selection, copying the value into the field.
        $driver->keyDown($xpath, 40);
        $driver->keyUp($xpath, 40);
        $driver->keyDown($xpath, 13);
        $driver->keyUp($xpath, 13);
    }


    /**
     *
     *
     *
     *
     *
     *  DrupalContext
     *
     *
     *
     *
     *
     *
     *
     *
     */

    /**
     * Attempts to find a link in a table row containing giving text. This is for
     * administrative pages such as the administer content types screen found at
     * `admin/structure/types`.
     *
     * @Given I click :link in the :rowText row
     * @Then I (should )see the :link in the :rowText row
     * @param $link
     * @param $rowText
     * @throws Exception
     */
    public function assertClickInTableRow($link, $rowText) {
        $page = $this->getSession()->getPage();
        if ($link_element = $this->getTableRow($page, $rowText)->findLink($link)) {
            // Click the link and return.
            $link_element->click();
            return;
        }
        throw new Exception(sprintf('Found a row containing "%s", but no "%s" link on the page %s', $rowText, $link, $this->getSession()->getCurrentUrl()));
    }

    /**
     * Retrieve a table row containing specified text from a given element.
     *
     * @param Element $element
     * @param string $search
     *   The text to search for in the table row.
     *
     * @return NodeElement
     *
     * @throws Exception
     */
    public function getTableRow(Element $element, $search) {
        $rows = $element->findAll('css', 'tr');
        if (empty($rows)) {
            throw new Exception(sprintf('No rows found on the page %s', $this->getSession()->getCurrentUrl()));
        }
        foreach ($rows as $row) {
            if (strpos($row->getText(), $search) !== FALSE) {
                return $row;
            }
        }
        throw new Exception(sprintf('Failed to find a row containing "%s" on the page %s', $search, $this->getSession()->getCurrentUrl()));
    }

    /**
     * Find text in a table row containing given text.
     *
     * @Then I should see (the text ):text in the :rowText row
     * @param $text
     * @param $rowText
     * @throws Exception
     */
    public function assertTextInTableRow($text, $rowText) {
        $row = $this->getTableRow($this->getSession()->getPage(), $rowText);
        if (strpos($row->getText(), $text) === FALSE) {
            throw new Exception(sprintf('Found a row containing "%s", but it did not contain the text "%s".', $rowText, $text));
        }
    }

    /**
     * @param $path
     * @param $element
     * @throws Exception
     * @Given I attach remote file :file to :element
     */
    public function attachFile($path,$element)
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
            if (is_file($fullPath)) {
                $path = $fullPath;
            }
            else {
                throw new Exception("The path you entered is not pointing to a file");
            }
        } else {
            throw new Exception("Files path parameter is not set");
        }
        /** @var Selenium2Driver $driver */
        $driver = $this->getSession()->getDriver();
        try{
        $element = $driver->getWebDriverSession()->element(
            'xpath', $this->getSession()->getSelectorsHandler()->selectorToXpath('named_exact',array('field', $element)));
        $path = $this->uploadFile($path);

        $element->postValue(array('value' => array($path)));
        } catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Uploads a file to the Selenium instance.
     *
     * @param string $path The path to the file to upload.
     * @return string The remote path.
     *
     * @throws Exception
     */
    public function uploadFile($path) {
        if (!is_file($path)) {
            throw new Exception('Could not upload file, the file: ' . $path . '. was not found.');
        }
        // Selenium only accepts uploads that are compressed as a Zip archive.
        $temp_filename = tempnam('', 'WebDriverZip');
        $archive = new ZipArchive();
        $archive->open($temp_filename, ZipArchive::CREATE);
        $archive->addFile($path, basename($path));
        $archive->close();
        /** @var Selenium2Driver $driver */
        $driver = $this->getSession()->getDriver();
        // Note that uploading files is not part of the official WebDriver
        // specification, but it is supported by Selenium.
        // @see https://github.com/SeleniumHQ/selenium/blob/master/py/selenium/webdriver/remote/webelement.py#L533
        $remote_path = $driver->getWebDriverSession()->file([
            'file' => base64_encode(file_get_contents($temp_filename)),
        ]);
        unlink($temp_filename);
        return $remote_path;
    }

    /**
     *
     *
     * Drupal8 Specific steps
     *
     *
     */

    /**
     * Expand hidden dropdown buttons in Drupal 8.
     * @When I expand hidden dropdown buttons in Drupal 8
     */
    public function iExpandHiddenDropdownButtonsInDrupal8() {
        $function = <<<SCRIPT
    (function($){
      var elements = $(document).find(".dropbutton-multiple");
      elements.addClass("open");
    })(jQuery);
SCRIPT;
        $this->getSession()->executeScript($function);
    }


    /**
     * @When I fill in Drupal 8 field :arg1 with :arg2
     * @param $field
     * @param $text
     * @throws Exception
     */
    public function iFillInDrupal8FieldWith($field, $text) {
        $target = $this->getFullIDbyPartialFirstPart($field);
        #echo "The text to be filled in is: ".$text;
        $this->fillField($target, $text);
    }

    /**
     * Fills in form field with specified id|name|label|value
     * Example: When I fill in "username" with: "bwayne"
     * Example: And I fill in "bwayne" for "username"
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" from yml$/
     * @param $field
     * @param $value
     */
    public function fillFieldWithValueFromYml($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->params[$value];
        MinkContext::fillField($field, $value);
    }

    /**
     *
     * @When I attach the file :arg1 in the Drupal 8 field with id :arg2
     * @param $file
     * @param $field
     * @throws Exception
     */
    public function iAttachFileInTheDrupal8FieldWithID($file, $field) {
        $target = $this->getFullIDbyPartialFirstPart($field);
        $this->attachFileToField($target, $file);
    }

    /**
     * @param $path
     * @param $element
     * @throws Exception
     * @Given I attach remote file :file to Drupal 8 field :field
     */
    public function attachFileToDrupal8Field($path,$element)
    {
        $this->attachFile($path, $this->getFullIDbyPartialFirstPart($element));
    }

    /**
     * I write some text into Drupal 8 WYSIWYG editor
     *
     * @Given I write :arg1 into Drupal 8 :arg2 wysiwyg
     * @param $text
     * @param $target
     * @throws Exception
     */

    public function iWriteTextIntoDrupal8wysiwyg($text, $target) {
        $target = $this->getFullIDbyPartialFirstPart($target);
        $this->writeElementText($text, $target);
    }

    /**
     * Press the "Edit" button for a paragraph within a node with provided ID
     *
     * @Given I press the edit button for paragraph with id :arg1
     * @param $id
     * @throws Exception if it's unable to find the id.
     */
    public function iPressTheEditButtonForParagraphWithID($id) {
        $target = $this->getFullIDbyPartialFirstPart($id);
        $this->pressButton2($target);
    }


    /**
     * Press the Drupal 8 button
     *
     * @Given I press the :arg1 Drupal 8 button
     * @param $button
     * @throws Exception
     */
    public function iPressTheDrupal8Button($button) {
        $target = $this->getFullIDbyPartialFirstPart($button);
        $this->pressButton2($target);
    }

    /**
     * Check Drupal 8 checkbox
     *
     * @Given I check the :arg1 Drupal 8 checkbox
     * @param $checkbox
     * @throws Exception
     */
    public function iCheckTheDrupal8Box($checkbox) {
        $target = $this->getFullIDbyPartialFirstPart($checkbox);
        $this->assertCheckBox2($target);
    }

    /**
     * Uncheck Drupal 8 checkbox
     *
     * @Given I uncheck the :arg1 Drupal 8 checkbox
     * @param $checkbox
     * @throws Exception
     */
    public function iUncheckTheDrupal8Box($checkbox) {
        $target = $this->getFullIDbyPartialFirstPart($checkbox);
        $this->assertUncheckBox2($target);
    }

    /**
     * Selects option in select field with specified id|name|label|value
     * Example: When I select "Bats" from "user_fears"
     * Example: And I select "Bats" from "user_fears"
     *
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from Drupal 8 "(?P<select>(?:[^"]|\\")*)"$/
     * @param $option
     * @param $target
     * @throws Exception
     */

    public function iSelectFromDrupal8($option, $target) {
        $target = $this->getFullIDbyPartialFirstPart($target);
        $this->selectOption($target, $option);
    }

    /**
     * Selects additional option in select field with specified id|name|label|value
     * Example: When I additionally select "Deceased" from "parents_alive_status"
     * Example: And I additionally select "Deceased" from "parents_alive_status"
     *
     * @When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from Drupal 8 "(?P<select>(?:[^"]|\\")*)" field$/
     * @param $select
     * @param $option
     * @throws Exception
     */
    public function additionallySelectOptionFromDrupal8Field($select, $option)
    {
        $select = $this->getFullIDbyPartialFirstPart($select);
        $this->additionallySelectOption($select, $option);
    }

    /**
     * @When I assert that Drupal 8 element with id :elementIdentifier has :attribute attribute with :value value
     * @param $elementIdentifier
     * @param $attribute
     * @param $value
     * @throws Exception
     */
    public function iAssertThatDrupal8ElementWithIdHasAttributeWithValue($elementIdentifier, $attribute, $value) {
        $elementIdentifier = $this->getFullIDbyPartialFirstPart($elementIdentifier);
        $this->iAssertThatElementWithIdHasAttributeWithValue($elementIdentifier, $attribute, $value);
    }

    /**
     * Filling a field with date. Use 0 for today and any higher number for the number of days in the future
     *
     * @When I fill :field field with date :days and store it to :variable variable
     * @param string $field
     * @param string $timeString use "+1 day", "+2 days" etc
     * @param string $variable
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function fillFieldWithDate($field, $timeString, $variable)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find('css', $field);
        if (null === $element) {
            throw new InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $field));
        }

        $model = $element->getAttribute('ng-model');
        $dateValue = (new DateTime())->setTimestamp(strtotime($timeString));
        list ($day, $month, $year) = explode('-', $dateValue->format('d-M-Y'));

        $script = <<<SCRIPT
            (function(){
                var element, container, result = [];

                result.push((element = jQuery("input[ng-model=\"{$model}\"]").click()).length);
                result.push((container = jQuery("div.date-picker-date-time[date-picker=\"{$model}\"]")).length);
                result.push(jQuery("th.switch.ng-binding", container).click().length);
                result.push(jQuery("span.ng-binding.ng-scope:contains(\"{$year}\")", container).click().length);
                result.push(jQuery("span.ng-binding.ng-scope:contains(\"{$month}\")", container).click().length);
                result.push(jQuery("span.ng-binding:contains(\"{$day}\")", container).not(".disabled").click().length);

                element.blur();

                return result.join("-");
            })();
SCRIPT;

        echo "$script";
        Assertions::assertEquals('1-1-1-1-1-1', $session->evaluateScript($script), "Failed selecting date from: `$field`");

        $dateValue = $dateValue->format('j-M-Y');
        $this->iStoreVariableWithValueToTheJsonFile($variable , $dateValue);
    }

    /**
     * Workaround fixes needed for latest versions of Chromedriver. Until Behat is updated to fully support W3C.
     * Those functions alongside with yml options should make test execution stable enough.
     */

    /**
     * Function override from MinkContext
     *
     * @param $link
     */
    public function clickLink($link)
    {
        MinkContext::clickLink($link);
        $this->getSession()->wait(500);

    }

    /**
     * Function override from MinkContext
     *
     * @param $button
     */
    public function pressButton($button)
    {
        MinkContext::pressButton($button);
        $this->getSession()->wait(500);

    }

    /**
     * Enter value character by character in specified time interval.
     *
     * @When I fill in :arg1 with :arg2 with :arg3 microseconds interval between each character
     *
     * @param $field
     * @param $value
     * @param $time
     * @throws Exception
     */
    public function fillSloMoField($field, $value, $time)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        // Disassemble the value in array character by character.
        $value_chunks = str_split($value);
        $xpath = $this->getSession()->getPage()->findField($field)->getXpath();
        if ($xpath) {
            foreach ($value_chunks as $value_chunk) {
                // Append each character after another and fill the field.
                $this->getSession()->getDriver()->getWebDriverSession()
                    ->element('xpath', $xpath)->postValue(['value' => [$value_chunk]]);
                // Wait in microseconds.
                usleep($time);
            }
        } else {
            $message = 'No element found for this name, id or label -> ' . $field;
            throw new Exception($message);
        }
    }

}
