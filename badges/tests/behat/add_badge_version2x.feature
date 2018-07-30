@core @core_badges @badge_version2
Feature: Add badges to the system
  In order to give badges to users for their achievements
  As an admin
  I need to manage badges in the system
  Background:
    Given I am on homepage
    And I log in as "admin"
  @javascript
  Scenario: Setting badges settings
    Given I navigate to "Badges settings" node in "Site administration > Badges"
    And I set the field "Default badge issuer name" to "Test Badge Site Version 2.x"
    And I set the field "Default badge issuer contact details" to "testuser@example.com"
    And I press "Save changes"
    And I follow "Badges"
    When I follow "Add a new badge"
    Then the field "issuercontact" matches value "testuser@example.com"
    And the field "issuername" matches value "Test Badge Site Version 2.x"
  @javascript
  Scenario: Accessing the badges
    And I press "Customise this page"
  # TODO MDL-57120 site "Badges" link not accessible without navigation block.
    And I add the "Navigation" block if not present
    Given I navigate to "Site badges" node in "Site pages"
    Then I should see "There are no badges available."
  @javascript @_file_upload
  Scenario: Add a badge version 2.x
    Given I navigate to "Add a new badge" node in "Site administration > Badges"
    And I set the following fields to these values:
      | Name | Test Badge Version 2.x|
      | Open Badges Specification Version | Version 2.x |
      | version | Version 2 |
      | Language | English |
      | Description | Test badge version 2.x description |
      | issuername | Test Badge Version 2.x Site |
      | issuercontact | testuser@example.com |
    And I upload "badges/tests/behat/badge1.png" file to "Image" filemanager
    When I press "Create badge"
    Then I should see "Edit details"
    And I should see "Test Badge Version 2.x"
    And I should see "Endorsement"
    And I should see "Related badges (0)"
    And I should see "Competencies (0)"
    And I should not see "Create badge"
    And I follow "Manage badges"
    And I should see "Number of badges available: 1"
    And I should not see "There are no badges available."
  @javascript @_file_upload
  Scenario: Add a badge version 2.x related
    Given I navigate to "Add a new badge" node in "Site administration > Badges"
    And I set the following fields to these values:
      | Name | Test Badge Version 2.x related |
      | Open Badges Specification Version | Version 2.x |
      | version | Version 2 |
      | Language | French |
      | Description | Test badge version 2.x related description |
      | issuername | Test Badge Version 2.x Site |
      | issuercontact | testuser@example.com |
    And I upload "badges/tests/behat/badge2.png" file to "Image" filemanager
    And I press "Create badge"
    And I wait until the page is ready
    And I follow "Manage badges"
    And I should see "Number of badges available: 1"
    And I press "Add a new badge"
    And I set the following fields to these values:
      | Name | Test Badge Version 2.x|
      | Open Badges Specification Version | Version 2.x |
      | version | Version 2 |
      | Language | English |
      | Description | Test badge version 2.x description |
      | issuername | Test Badge Version 2.x Site |
      | issuercontact | testuser@example.com |
    And I upload "badges/tests/behat/badge1.png" file to "Image" filemanager
    And I press "Create badge"
    And I follow "Related badges (0)"
    And I should see "This badge does not have an related badge."
    And I press "Add related badge"
    And I follow "Related badges"
    And I wait until the page is ready
    And I follow "Related badges"
    And I set the field "relatedbadgeids[]" to "Test Badge Version 2.x related (version: Version 2, language: French)"
    When I press "Save changes"
    Then I should see "Related badges (1)"
  @javascript
  Scenario: Enrolment badge version 2.x
    Given I navigate to "Add a new badge" node in "Site administration > Badges"
    And I set the following fields to these values:
      | Name | Test Badge Version 2.x |
      | Open Badges Specification Version | Version 2.x |
      | version | Version 2 |
      | Language | English |
      | Description | Test badge version 2.x description |
      | issuername | Test Badge Version 2.x Site |
      | issuercontact | testuser@example.com |
    And I upload "badges/tests/behat/badge1.png" file to "Image" filemanager
    When I press "Create badge"
    Then I should see "Edit details"
    And I should see "Endorsement"
    And I follow "Endorsement"
   # Website required https protocol
    And I set the following fields to these values:
      | Name | Endorser |
      | Email | endorsement@example.com |
      | Website | https://example.com  |
      | Claim URL | https://claimurl.example.com |
      | Endorsement Comment | Test Endorsement Comment |
    And I press "Save changes"
  @javascript
  Scenario: Competencies alignment for Badges verison 2.x
    Given I navigate to "Add a new badge" node in "Site administration > Badges"
    And I set the following fields to these values:
      | Name | Test Badge Version 2.x |
      | Open Badges Specification Version | Version 2.x |
      | version | Version 2 |
      | Language | English |
      | Description | Test badge version 2.x description |
      | issuername | Test Badge Version 2.x Site |
      | issuercontact | testuser@example.com |
    And I upload "badges/tests/behat/badge1.png" file to "Image" filemanager
    When I press "Create badge"
    Then I should see "Test Badge Version 2.x"
    And I should see "Endorsement"
    And I should see "Related badges (0)"
    And I should see "Competencies (0)"
    And I follow "Competencies (0)"
    And I should see "This badge does not have an competencies alignment."
    And I press "Add competency alignment"
    And I follow "Competencies alignment"
    And I wait until the page is ready
    And I follow "Competencies alignment"
    And I set the following fields to these values:
      | Name | Test Badge Competencies Version 2.x |
      | URL | https://competencies.example.com |
      | Description | Test Badge Competencies Version 2.x description |
    When I press "Save changes"
    And I should see "Competencies (1)"
