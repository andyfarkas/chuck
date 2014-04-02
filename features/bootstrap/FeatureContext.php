<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends \Behat\MinkExtension\Context\MinkContext
{
    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }


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
