@block @block_interactembed @_file_upload @_switch_iframe
Feature: Configure and display InteractEmbed blocks
  In order to publish interactive content outside an activity
  As an editing teacher
  I need to configure an InteractEmbed block safely

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  @javascript
  Scenario: Add a click-to-load web content block
    When I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title         | Behat interactive content                       |
      | config_sourcetype    | url                                               |
      | config_sourceurl     | https://www.youtube.com/watch?v=aqz-KE-bpKQ      |
      | config_aspectratio   | 16x9                                             |
      | config_displaymode   | inline                                           |
      | config_loadonrequest | 1                                                |
      | config_showfullscreen| 1                                                |
    Then I should see "Load interactive content" in the "Behat interactive content" "block"
    And I should see "Open content in a new window" in the "Behat interactive content" "block"

  @javascript
  Scenario: Display two independent interactive blocks on the same page
    Given I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title       | First independent guide  |
      | config_sourcetype  | guide                    |
      | config_aspectratio | 16x9                     |
      | config_displaymode | inline                   |
    And I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title       | Second independent guide |
      | config_sourcetype  | guide                    |
      | config_aspectratio | 4x3                      |
      | config_displaymode | inline                   |
    Then "First independent guide" "block" should exist
    And "Second independent guide" "block" should exist
    And "iframe.block-interactembed-iframe" "css_element" should exist in the "First independent guide" "block"
    And "iframe.block-interactembed-iframe" "css_element" should exist in the "Second independent guide" "block"

  @javascript
  Scenario: Convert the source from pasted iframe markup
    When I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title       | Pasted iframe content                                                        |
      | config_sourcetype  | url                                                                          |
      | config_sourceurl   | <iframe src="https://www.youtube.com/watch?v=aqz-KE-bpKQ"></iframe>         |
      | config_aspectratio | 16x9                                                                         |
      | config_displaymode | inline                                                                       |
    Then "Pasted iframe content" "block" should exist
    And the "src" attribute of "iframe.block-interactembed-iframe" "css_element" should contain "youtube-nocookie.com/embed/aqz-KE-bpKQ"

  @javascript
  Scenario: Expose copyable same-Moodle embed code only when enabled
    When I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title         | Reusable guide |
      | config_sourcetype    | guide          |
      | config_aspectratio   | 16x9           |
      | config_displaymode   | inline         |
      | config_showembedcode | 1              |
    Then I should see "Embed code" in the "Reusable guide" "block"
    And ".block-interactembed-embedtools textarea[readonly]" "css_element" should exist in the "Reusable guide" "block"
    And I click on "summary" "css_element" in the "Reusable guide" "block"
    And I should see "Copy embed code" in the "Reusable guide" "block"

  @javascript
  Scenario: Edit an existing web content block
    Given I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title       | Original interactive content                  |
      | config_sourcetype  | url                                           |
      | config_sourceurl   | https://www.youtube.com/watch?v=aqz-KE-bpKQ  |
      | config_aspectratio | 16x9                                          |
      | config_displaymode | inline                                        |
    When I configure the "Original interactive content" block
    And I set the field "config_title" to "Updated interactive content"
    And I set the field "config_aspectratio" to "Classic (4:3)"
    And I press "Save changes"
    Then "Updated interactive content" "block" should exist
    And "Original interactive content" "block" should not exist

  @javascript
  Scenario: Upload and display a self-contained HTML5 package
    Given I add the "InteractEmbed — Interactive content block..." block
    And I set the field "config_title" to "Packaged interactive content"
    And I set the field "config_sourcetype" to "HTML5 package"
    And I upload "blocks/interactembed/tests/fixtures/interactembed-behat-package.zip" file to "HTML5 package files" filemanager
    And I press "Save changes"
    When I wait until "block-interactembed-iframe" class iframe is interactable and switch to it
    Then I should see "InteractEmbed Behat package"

  @javascript
  Scenario: Display the included guide on the public site home page
    Given the following config values are set as admin:
      | enablemyhome | 1 |
      | forcelogin   | 0 |
    And I log out
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "InteractEmbed — Interactive content block" block to the default region with:
      | config_title       | Public InteractEmbed guide |
      | config_sourcetype  | guide                      |
      | config_aspectratio | 16x9                       |
      | config_displaymode | inline                     |
    And I turn editing mode off
    And I log out
    When I am on site homepage
    Then "Public InteractEmbed guide" "block" should exist
    And "iframe.block-interactembed-iframe" "css_element" should exist

  @javascript
  Scenario: Apply administrative defaults to a new block
    Given the following config values are set as admin:
      | defaultaspectratio   | 4x3     | block_interactembed |
      | defaultdisplaymode   | card    | block_interactembed |
      | defaultloadonrequest | 1       | block_interactembed |
      | defaultsandboxprofile | strict | block_interactembed |
    And I add the "InteractEmbed — Interactive content block..." block
    Then the following fields match these values:
      | config_aspectratio   | Classic (4:3)                       |
      | config_displaymode   | Compact card, then open the player  |
      | config_loadonrequest | 1                                   |
      | config_sandboxprofile | Strict                              |

  @javascript
  Scenario: Enforce a package-only site policy in the edit form
    Given the following config values are set as admin:
      | sourcepolicy      | packageonly | block_interactembed |
      | allowembedcode    | 0           | block_interactembed |
      | forceremoteconsent| 1           | block_interactembed |
    And I add the "InteractEmbed — Interactive content block..." block
    Then the following fields match these values:
      | config_sourcetype | HTML5 package |
    And "config_showembedcode" "field" should not exist
    And I should see "The administrator requires visitor interaction before all remote content is loaded."
