@javascript
Feature: Example

  @custom_tag
  Scenario: Search for Behat in Google
    Given I am on "https://google.com"
    And I fill in "q" with "Behat"
    And I click on the element with css selector ".gNO89b" using JS
    And I take a screenshot Behat_Results
