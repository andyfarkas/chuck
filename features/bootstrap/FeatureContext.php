<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Features context.
 */
class FeatureContext extends \Behat\MinkExtension\Context\MinkContext
{
    /**
     * @Given /^I am on "([^"]*)" svn log page$/
     */
    public function iAmOnSvnLogPage($project)
    {
        return array(
            new \Behat\Behat\Context\Step\Given("I am on homepage"),
            new \Behat\Behat\Context\Step\When("I select \"{$project}\" from \"project\""),
            new \Behat\Behat\Context\Step\When("I press \"Select\""),
            new \Behat\Behat\Context\Step\Then("I should see \"Project:\""),
            new \Behat\Behat\Context\Step\When("I follow \"Log\""),
        );
    }
}
