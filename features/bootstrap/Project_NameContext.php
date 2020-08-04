<?php

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Context\Context;


class Project_NameContext implements Context
{
    /** @var FeatureContext */
    private $featureContext;

    /** @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var Behat\Behat\Context\Environment\InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        $this->featureContext = $environment->getContext('FeatureContext');
    }
}
