Gravity Forms REST API Feature Add-On
==============================

[![Build Status](https://travis-ci.com/gravityforms/gravityformsrestapi.svg?token=dWdigWFPjUjwVzDjbyxv&branch=master)](https://travis-ci.com/gravityforms/gravityformsrestapi)

## Introduction

This is the development version of the Gravity Forms Web API version 2. When it's ready it'll be integrated into the
Gravity Forms core.

**This is currently a beta version and should not be run on production sites without a full understanding of the
possible risks that may be encountered when running beta software.**

## Authentication

The recommended and supported methods of authentication are OAuth 1.0a or WordPress cookie authentication. For
information on these, see the following:

[Gravity Forms REST API Authentication](https://www.gravityhelp.com/documentation/article/web-api/#authentication)

Authentication can also be performed using the same methods as the WordPress REST API. For further information on
WordPress' authentication, the following resources are available:

* [WordPress REST API authentication documentation](http://v2.wp-api.org/guide/authentication/)
* [WP REST API: Setting Up and Using Basic Authentication](https://code.tutsplus.com/tutorials/wp-rest-api-setting-up-and-using-basic-authentication--cms-24762)
* [WP REST API: Setting Up and Using OAuth 1.0a Authentication](https://code.tutsplus.com/tutorials/wp-rest-api-setting-up-and-using-oauth-10a-authentication--cms-24797)



**NOTE: When using a browser to test your API calls, be aware that if you are currently logged in to the WordPress dashboard, the cookie authentication will override any other authentication method. In that situation, any other authentication method will result in a "acess denied" error.**


### Signature Generation

#### PHP

```php
function calculate_signature($string, $private_key) {
    $hash = hash_hmac("sha1", $string, $private_key, true);
    $sig  = rawurlencode(base64_encode($hash));
    
    return $sig;
}
    
$api_key        = "1234";
$private_key    = "abcd";
$method         = "GET";
$route          = "forms/1/entries";
$expires        = strtotime("+60 mins");
$string_to_sign = sprintf("%s:%s:%s:%s", $api_key, $method, $route, $expires);
$sig            = calculate_signature($string_to_sign, $private_key);
```

The signature would then be located within the *$sig* variable.

#### JavaScript

```html
<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/hmac-sha1.js"></script>
<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/components/enc-base64-min.js"></script>
<script type="text/javascript">
   
    function CalculateSig(stringToSign, privateKey){
        var hash = CryptoJS.HmacSHA1(stringToSign, privateKey);
        var base64 = hash.toString(CryptoJS.enc.Base64);
        return encodeURIComponent(base64);
    }
 
    var d = new Date,
         expiration = 3600 // 1 hour,
         unixtime = parseInt(d.getTime() / 1000),
         future_unixtime = unixtime + expiration,
         publicKey = "1234",
         privateKey = "abcd",
         method = "GET",
         route = "forms/1/entries";
  
    stringToSign = publicKey + ":" + method + ":" + route + ":" + future_unixtime;
    sig = CalculateSig(stringToSign, privateKey);
</script>
```
The signature would then be located within the *sig* variable.

#### CLI

```bash
echo -n "PUBLIC_KEY:METHOD:ROUTE:EXPIRES" | openssl dgst -sha1 -hmac "PRIVATE_KEY"
```
```bash
echo -n "1234:GET:forms/1/entries:3600" | openssl dgst -sha1 -hmac "abcd"
```
Here, the signature would be output within your terminal.
    
Note that the string will still need to be URL encoded. Encoding can be done using the --data-urlencode flag in curl.

#### C#

```csharp
using System;
using System.Web;
using System.Security.Cryptography;
using System.Text;
    
namespace GravityForms
{
    public class Sample
    {
        public static GenerateSignature()
        {
            string publicKey = "1234";
            string privateKey = "abcd";
            string method = "GET";
            string route = "forms/1/entries";
            string expires = Security.UtcTimestamp(new TimeSpan(0,1,0));
            string stringToSign = string.Format("{0}:{1}:{2}:{3}", publicKey, method, route, expires);

            var sig = Security.Sign(stringToSign, privateKey);
        }
    }
    
    public class Security
    {
    
        public static string UrlEncodeTo64(byte[] bytesToEncode)
        {
            string returnValue
                = System.Convert.ToBase64String(bytesToEncode);
 
            return HttpUtility.UrlEncode(returnValue);
        }

        public static string Sign(string value, string key)
        {
            using (var hmac = new HMACSHA1(Encoding.ASCII.GetBytes(key)))
            {
                return UrlEncodeTo64(hmac.ComputeHash(Encoding.ASCII.GetBytes(value)));
            }
        }
   
        public static int UtcTimestamp( TimeSpan timeSpanToAdd)
        {
            TimeSpan ts = (DateTime.UtcNow.Add(timeSpanToAdd) - new DateTime(1970,1,1,0,0,0));
            int expires_int =  (int) ts.TotalSeconds;
            return expires_int;
        }
    }
}
```
    
The signature would then be located within the *sig* variable.

## API Path

The API can be accessed as route from the WordPress REST API. This should look something like this:

    https://localhost/wp-json/gf/v2/
    
For example, to obtain the Gravity Forms entry with ID 5, your request would be made to the following:

    https://localhost/wp-json/gf/v2/entries/5
    
## Sending Requests

### PHP
```php
// Replace the value of this variable with your signature.
// See the Signature Generation section for more information.
$signature = '';
  
// Define the URL that will be accessed.
$url = 'https://localhost/wp-json/gf/v2/entries';
 
// Make the request to the API.
$response = wp_remote_request( urlencode( $url ), array( 'method' => 'GET' ) );
 
// Check the response code.
if ( wp_remote_retrieve_response_code( $response ) != 200 || ( empty( wp_remote_retrieve_body( $response ) ) ) ){
    // If not a 200, HTTP request failed.
    die( 'There was an error attempting to access the API.' );
}
   
// Result is in the response body and is json encoded.
$body = json_decode( wp_remote_retrieve_body( $response ), true );
 
// Check the response body.
if( $body['status'] > 202 ){
    die( "Could not retrieve forms." );
}
   
// Forms retrieved successfully
$forms = $body['response'];
```

In this example, the *$forms* variable contains the response from the API request.

## Routes

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

```bash
curl --data [EXAMPLE_DATA] --header "Content-Type: application/json" https://localhost/wp-json/gf/v2
```

### No Response Envelope

The response will not be enveloped by default. This means that the response will not be a JSON string containing the
"status" and "response" - the body will contain the response and the HTTP code will contain the status. 

The WP-API will envelope the response if the _envelope param is included in the request.

#### Example

**Standard response:**

```json
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
```
**Response with _envelope parameter:**

```json
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
```

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

```json
{
    "input_1": "test",
    "field_values" : "",
    "source_page": 1,
    "target_page": 0
}
```

### POST Single Resources

In order to maintain consistency with the WP API, the POST /entries and POST /forms endpoints no longer accept collections. This means that it's no longer possible to create multiple entries or forms in a single request.

## Unit Tests

The unit tests can be installed from the terminal using:

```bash
./tests/bin/install.sh [DB_NAME] [DB_USER] [DB_PASSWORD] [DB_HOST]
```

If you're using [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV) you can use this command:

```bash
./tests/bin/install.sh wordpress_unit_tests root root localhost
```