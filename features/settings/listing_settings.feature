@settings
Feature: Checking if settings work correctly
  In order to manage settings
  As a HTTP Client
  I want to be able to read settings via API by different scopes

  Scenario: Listing all settings by different scopes
    Given I am authenticated as "test.user"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/{version}/settings/"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "type":"boolean",
        "scope":"tenant",
        "value":true,
        "name":"registration_enabled"
      },
      {
        "value":"@FOSUser\/Registration\/email.txt.twig",
        "scope":"tenant",
        "type":"string",
        "name":"registration_confirmation.template"
      },
      {
        "value":{
          "contact@publisher.test":"Publisher"
        },
        "scope":"tenant",
        "type":"array",
        "name":"registration_from_email.confirmation"
      },
      {
        "value":"@FOSUser\/Resetting\/email.txt.twig",
        "scope":"tenant",
        "type":"string",
        "name":"registration_resetting.template"
      },
      {
        "value":{
          "contact@publisher.test":"Publisher"
        },
        "scope":"tenant",
        "type":"array",
        "name":"registration_from_email.resetting"
      },
      {
        "scope":"global",
        "type":"string",
        "value":"Publisher Master",
        "name":"instance_name"
      },
      {
        "scope":"user",
        "type":"string",
        "value":"{}",
        "name":"filtering_prefrences"
      },
      {
        "scope":"user",
        "type":"string",
        "value":"{}",
        "name":"user_private_preferences"
      },
      {
        "scope":"user",
        "type":"string",
        "value":"{}",
        "name":"user_favourite_articles"
      },
      {
        "value":"",
        "scope":"theme",
        "type":"string",
        "name":"theme_logo"
      },
      {
        "label":"Primary Font Family",
        "value":"Roboto",
        "type":"string",
        "options":[
          {
            "value":"Roboto",
            "label":"Roboto"
          },
          {
            "value":"Lato",
            "label":"Lato"
          },
          {
            "value":"Oswald",
            "label":"Oswald"
          }
        ],
        "scope":"theme",
        "name":"primary_font_family"
      },
      {
        "label":"Secondary Font Family",
        "value":"Roboto",
        "type":"string",
        "options":[
          {
            "value":"Roboto",
            "label":"Roboto"
          },
          {
            "value":"Lato",
            "label":"Lato"
          },
          {
            "value":"Oswald",
            "label":"Oswald"
          }
        ],
        "scope":"theme",
        "name":"secondary_font_family"
      },
      {
        "label":"Body Font Size",
        "value":14,
        "type":"integer",
        "scope":"theme",
        "name":"body_font_size"
      }
    ]
    """
