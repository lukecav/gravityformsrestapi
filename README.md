Gravity Forms REST API Feature Add-On
==============================

[![Build Status](https://travis-ci.com/gravityforms/gravityformsrestapi.svg?token=dWdigWFPjUjwVzDjbyxv&branch=master)](https://travis-ci.com/gravityforms/gravityformsrestapi)

## Introduction

This is the development version of the Gravity Forms Web API version 2. When it's ready it'll be integrated into the
Gravity Forms core.

**This is currently a beta version and should not be run on production sites without a full understanding of the
possible risks that may be encountered when running beta software.**

## Authentication

The recommended and supported methods of authentication are OAuth 1.0a or WordPress cookie authentication. For information on these, see the following:

[Gravity Forms REST API Authentication](https://www.gravityhelp.com/documentation/article/web-api/#authentication)

Authentication can also be performed using the same methods as the WordPress REST API. For further information on
WordPress' authentication, the following resources are available:

* [WordPress REST API authentication documentation](http://v2.wp-api.org/guide/authentication/)
* [WP REST API: Setting Up and Using Basic Authentication](https://code.tutsplus.com/tutorials/wp-rest-api-setting-up-and-using-basic-authentication--cms-24762)
* [WP REST API: Setting Up and Using OAuth 1.0a Authentication](https://code.tutsplus.com/tutorials/wp-rest-api-setting-up-and-using-oauth-10a-authentication--cms-24797)

## API Path

The API can be accessed as an endpoint from the WordPress REST API. This should look something like this:

    https://localhost/wp-json/gf/v2/
    
For example, to obtain the Gravity Forms entry with ID 5, your request would be made to the following:

    https://localhost/wp-json/gf/v2/entries/5

## Endpoints

### /entries

    https://localhost/wp-json/gf/v2/entries

#### GET

Gets all entries.

##### Arguments

* **labels** 
  * **Description:** Whether to include the labels.
  * **Required:** false
* **paging**
  * **Description:** The paging criteria.
  * **Required:** false
* **search:**
  * **Description:** The search criteria.
  * **Required:** false
* **sorting:**
  * **Description:** The sorting criteria.
  * **Required:** false

#### POST

Inserts an entry.

##### Arguments

* **created_by** 
  * **Description:** The user ID of the entry submitter.
  * **Required:** false
* **date_created**
  * **Description:** The date the entry was created, in UTC.
  * **Required:** false
* **form_id:**
  * **Description:** The Form ID for the entry.
  * **Required:** true
* **ip:**
  * **Description:** The IP address of the entry creator.
  * **Required:** false
* **is_fulfilled:**
  * **Description:** Whether the transaction has been fulfilled, if applicable.
  * **Required:** false
* **is_read:**
  * **Description:** Whether the entry has been read.
  * **Required:** false
* **is_starred:**
  * **Description:** Whether the entry is starred.
  * **Required:** false
* **payment_amount:**
  * **Description:** The amount of the payment, if applicable.
  * **Required:** false
* **payment_date:**
  * **Description:** The date of the payment, if applicable.
  * **Required:** false
* **payment_method:**
  * **Description:** The payment method for the payment, if applicable.
  * **Required:** false
* **payment_status:**
  * **Description:** The status of the payment, if applicable.
  * **Required:** false
* **source_url:**
  * **Description:** The URL where the form was embedded.
  * **Required:** false
* **status:**
  * **Description:** The status of the entry.
  * **Required:** false
* **transaction_id:**
  * **Description:** The transaction ID for the payment, if applicable.
  * **Required:** false
* **transaction_type:**
  * **Description:** The type of the transaction, if applicable.
  * **Required:** false
* **user_agent:**
  * **Description:** The user agent string for the browser used to submit the entry.
  * **Required:** false

### /entries/[ENTRY_ID]

#### GET

Gets the details of a specific entry.

##### Arguments

This method does not have any arguments.

#### POST

Creates an entry with the specified ID.

##### Arguments

* **created_by** 
  * **Description:** The user ID of the entry submitter.
  * **Required:** false
* **date_created**
  * **Description:** The date the entry was created, in UTC.
  * **Required:** false
* **form_id:**
  * **Description:** The Form ID for the entry.
  * **Required:** true
* **ip:**
  * **Description:** The IP address of the entry creator.
  * **Required:** false
* **is_fulfilled:**
  * **Description:** Whether the transaction has been fulfilled, if applicable.
  * **Required:** false
* **is_read:**
  * **Description:** Whether the entry has been read.
  * **Required:** false
* **is_starred:**
  * **Description:** Whether the entry is starred.
  * **Required:** false
* **payment_amount:**
  * **Description:** The amount of the payment, if applicable.
  * **Required:** false
* **payment_date:**
  * **Description:** The date of the payment, if applicable.
  * **Required:** false
* **payment_method:**
  * **Description:** The payment method for the payment, if applicable.
  * **Required:** false
* **payment_status:**
  * **Description:** The status of the payment, if applicable.
  * **Required:** false
* **source_url:**
  * **Description:** The URL where the form was embedded.
  * **Required:** false
* **status:**
  * **Description:** The status of the entry.
  * **Required:** false
* **transaction_id:**
  * **Description:** The transaction ID for the payment, if applicable.
  * **Required:** false
* **transaction_type:**
  * **Description:** The type of the transaction, if applicable.
  * **Required:** false
* **user_agent:**
  * **Description:** The user agent string for the browser used to submit the entry.
  * **Required:** false
  
#### PUT

Updates a specific entry.

##### Arguments

* **created_by** 
  * **Description:** The user ID of the entry submitter.
  * **Required:** false
* **date_created**
  * **Description:** The date the entry was created, in UTC.
  * **Required:** false
* **form_id:**
  * **Description:** The Form ID for the entry.
  * **Required:** false
* **ip:**
  * **Description:** The IP address of the entry creator.
  * **Required:** false
* **is_fulfilled:**
  * **Description:** Whether the transaction has been fulfilled, if applicable.
  * **Required:** false
* **is_read:**
  * **Description:** Whether the entry has been read.
  * **Required:** false
* **is_starred:**
  * **Description:** Whether the entry is starred.
  * **Required:** false
* **payment_amount:**
  * **Description:** The amount of the payment, if applicable.
  * **Required:** false
* **payment_date:**
  * **Description:** The date of the payment, if applicable.
  * **Required:** false
* **payment_method:**
  * **Description:** The payment method for the payment, if applicable.
  * **Required:** false
* **payment_status:**
  * **Description:** The status of the payment, if applicable.
  * **Required:** false
* **source_url:**
  * **Description:** The URL where the form was embedded.
  * **Required:** false
* **status:**
  * **Description:** The status of the entry.
  * **Required:** false
* **transaction_id:**
  * **Description:** The transaction ID for the payment, if applicable.
  * **Required:** false
* **transaction_type:**
  * **Description:** The type of the transaction, if applicable.
  * **Required:** false
* **user_agent:**
  * **Description:** The user agent string for the browser used to submit the entry.
  * **Required:** false
  
#### DELETE

Deletes an entry.

##### Arguments

This method does not have any arguments.

### /entries/[ENTRY_ID]/fields/[FIELD_ID]

#### GET

Gets the entry details for a specific field, in a specific entry.

##### Arguments

This method does not have any arguments.

### /forms

#### GET

Gets the details of all forms.

##### Arguments

This method does not have any arguments.

#### POST

Creates a form.

##### Arguments

This method does not have any arguments.

### /forms/[FORM_ID]

#### GET

Gets the form details for a specific form.

##### Arguments

This method does not have any arguments.

### /forms/[FORM_ID]/entries

#### GET

Gets the entries for a specific form ID.

##### Arguments

* **labels**
  * **Description:** Whether to include the labels.
  * **Required:** false
* **paging**
  * **Description:** The paging criteria.
  * **Required:** false
* **search**
  * **Description:** The search criteria.
  * **Required:** false
* **sorting**
  * **Description:** The sorting criteria.
  * **Required:** false

#### POST

Creates an entry to be associated with a form.

##### Arguments

* **created_by** 
  * **Description:** The user ID of the entry submitter.
  * **Required:** false
* **date_created**
  * **Description:** The date the entry was created, in UTC.
  * **Required:** false
* **form_id:**
  * **Description:** The Form ID for the entry.
  * **Required:** false
* **ip:**
  * **Description:** The IP address of the entry creator.
  * **Required:** false
* **is_fulfilled:**
  * **Description:** Whether the transaction has been fulfilled, if applicable.
  * **Required:** false
* **is_read:**
  * **Description:** Whether the entry has been read.
  * **Required:** false
* **is_starred:**
  * **Description:** Whether the entry is starred.
  * **Required:** false
* **payment_amount:**
  * **Description:** The amount of the payment, if applicable.
  * **Required:** false
* **payment_date:**
  * **Description:** The date of the payment, if applicable.
  * **Required:** false
* **payment_method:**
  * **Description:** The payment method for the payment, if applicable.
  * **Required:** false
* **payment_status:**
  * **Description:** The status of the payment, if applicable.
  * **Required:** false
* **source_url:**
  * **Description:** The URL where the form was embedded.
  * **Required:** false
* **status:**
  * **Description:** The status of the entry.
  * **Required:** false
* **transaction_id:**
  * **Description:** The transaction ID for the payment, if applicable.
  * **Required:** false
* **transaction_type:**
  * **Description:** The type of the transaction, if applicable.
  * **Required:** false
* **user_agent:**
  * **Description:** The user agent string for the browser used to submit the entry.
  * **Required:** false

### /forms/[FORM_ID]/entries/fields/[FIELD_ID]

#### GET

Gets the entry details for a specific field within a form.

##### Arguments

This method does not have any arguments.
 
### /forms/[FORM_ID]/results

#### GET

Searches through a form for a value.

##### Arguments

* **search**
  * **Description:** The search criteria.
  * **Required:** true

### /forms/[FORM_ID]/results/schema

#### GET

##### Arguments

This method does not have any arguments.

### /forms/[FORM_ID]/submissions

#### POST

Submits the specified form ID with the specified values.

##### Arguments

* **field_values**
  * **Description:** The field values.
  * **Required:** false
* **input_[FIELD_ID]**
  * **Description:** The input values.
  * **Required** false
* **source_page**
  * **Description:** The source page number.
  * **Required:** false
* **target_page**
  * **Description:** The target page number.
  * **Required:** false

## Upgrading to Version 2

The API is largely the same as version 1. The endpoints are the same and the same "one-legged" OAuth 1.0a authentication mechanism is still supported.

The following breaking changes are required by clients to consume version 2:

### Specify the Content Type when appropriate

The content-type application/json must be specified when sending JSON.

#### Example

    curl --data [EXAMPLE_DATA] --header "Content-Type: application/json" https://localhost/wp-json/gf/v2

### No Response Envelope

The response will not be enveloped by default. This means that the response will not be a JSON string containing the
"status" and "response" - the body will contain the response and the HTTP code will contain the status. 

The WP-API will envelope the response if the _envelope param is included in the request.

#### Example

**Standard response:**

    {
        "3": "Drop Down First Choice",
        "created_by": "1",
        "currency": "USD",
        "date_created": "2016-10-10 18:06:12",
        "form_id": "1",
        "id": "1",
        "ip": "127.0.0.1",
        "is_fulfilled": null,
        "is_read": 0,
        "is_starred": 0,
        "payment_amount": null,
        "payment_date": null,
        "payment_method": null,
        "payment_status": null,
        "post_id": null,
        "source_url": "http://gftesting.dev/?gf_page=preview&id=1",
        "status": "active",
        "transaction_id": null,
        "transaction_type": null,
        "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36"
    }

**Response with _envelope parameter:**

    {
        "body": {
            "3": "Drop Down First Choice",
            "created_by": "1",
            "currency": "USD",
            "date_created": "2016-10-10 18:06:12",
            "form_id": "1",
            "id": "1",
            "ip": "127.0.0.1",
            "is_fulfilled": null,
            "is_read": 0,
            "is_starred": 0,
            "payment_amount": null,
            "payment_date": null,
            "payment_method": null,
            "payment_status": null,
            "post_id": null,
            "source_url": "http://gftesting.dev/?gf_page=preview&id=1",
            "status": "active",
            "transaction_id": null,
            "transaction_type": null,
            "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36"
        },
        "headers": {
            "Allow": "GET, POST, PUT, PATCH, DELETE"
        },
        "status": 200
    }

### WordPress Cookie Authentication Nonce

The "gf_api" nonce is no longer supported. Use the WordPress cookie authentication provided by the WP API instead. Create the nonce using wp_create_nonce( 'wp_rest' ) and send it in the _wpnonce data parameter (either POST data or in 
the query for GET requests), or via the X-WP-Nonce header.

### Form Submissions

The Form Submissions endpoint now accepts application/json, application/x-www-form-urlencoded and multipart/form-data 
content types. With the introduction of support for multipart/form-data now files can be sent to single file upload fields.

Request values should be sent all together instead of in separate elements for input_values, field_values, target_page 
and source_page.

#### Example

**Example body of a JSON request:**

    {
        "input_1": "test",
        "field_values" : "",
        "source_page": 1,
        "target_page": 0
     }

### POST Single Resources

In order to maintain consistency with the WP API, the POST /entries and POST /forms endpoints no longer accept collections. This means that it's no longer possible to create multiple entries or forms in a single request.

## Unit Tests

The unit tests can be installed from the terminal using:

    ./tests/bin/install.sh [DB_NAME] [DB_USER] [DB_PASSWORD] [DB_HOST]


If you're using [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV) you can use this command:

	./tests/bin/install.sh wordpress_unit_tests root root localhost
