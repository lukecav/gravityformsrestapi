Gravity Forms REST API Add-On
==============================

[![Build Status](https://travis-ci.com/gravityforms/gravityformsrestapi.svg?token=dWdigWFPjUjwVzDjbyxv&branch=master)](https://travis-ci.com/gravityforms/gravityformsrestapi)


## Unit Tests

The unit tests can be installed from the terminal using:

    bash tests/bin/install.sh [DB_NAME] [DB_USER] [DB_PASSWORD] [DB_HOST]


If you're using VVV you can use this command:

	bash tests/bin/install.sh wordpress_unit_tests root root localhost

## Upgrading to Version 2

The API is largely the same as version 1. The following breaking changes are required by clients to consume version 2:

### Specify the Content Type when appropriate

The content-type application/json must be specified when sending JSON.

### No Response Envelope

The response will not be enveloped by default. This means that the response with not be a JSON string containing the "status" and "response" - the body will contain the response and the HTTP code will contain the status. 

The request will be enveloped by the WP-API if the _envelope param is included in the request.

### WordPress Cookie Authentication Nonce

Create the nonce using wp_create_nonce( 'wp_rest' ) and send it in the _wpnonce data parameter (either POST data or in the query for GET requests), or via the X-WP-Nonce header.

### Form Submissions

The Form Submissions endpoint now accepts application/json, application/x-www-form-urlencoded and multipart/form-data content types. 

Request values should be sent all together instead of in separate elements for input_values, field_values, target_page and source_page.

Example body of a JSON request:

    {
        "input_1": "test",
        "field_values" : "",
        "source_page": 1,
        "target_page": 0
     }
