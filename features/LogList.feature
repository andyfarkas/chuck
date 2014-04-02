Feature: SVN log list
  In order to select revision from branch or tag to changelog
  As a Chuck user
  I need to be able to see a list of revisions loaded from repository

  Background: User on a log list
    Given I am on "fo-currys" svn log page
    Then I should see "Revision"
    And I should see "Author"
    And I should see "Date"
    And I should see "Message"

  Scenario: generate wiki documentation
    Given I am on "fo-currys" svn log page
    When I check "rev24954"
    And I check "rev24920"
    When I press "Generate Wiki log"
    Then I should see "Confluence editor"
    And I should see "|| || *UAT* || *PROD* ||"
    And I should see "|| Type || Priority || Key || Summary ||"

