Feature: checking in and checking out

  Scenario: Checking in
    Given a building was registered
    When the user checks into the building
    Then the user should have been checked into the building

  Scenario: Checking in twice causes a check-in anomaly to be raised
    Given a building was registered
    And the user checked into the building
    When the user checks into the building
    Then the user should have been checked into the building
    And a check-in anomaly should have been raised

  Scenario: Alice checks into the Grand Hotel Italia
    Given "Grand Hotel Italia" is a registered building
    When "Alice" checks into "Grand Hotel Italia"
    Then "Alice" should have been checked into "Grand Hotel Italia"
    