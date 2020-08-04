@javascript
Feature: Architecture

  @custom_tag
  Scenario: Login
    Given I am on "/user"
    And I wait for element with ".cc-dialog" selector to appear
    And I click on the element with css selector ".cc-primary-btn"
    And I log in
    And I take a screenshot LOGIN