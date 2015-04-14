## Get Image URL ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=imageGetUrl&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * imageId (required) - Id of the image.
  * barcode (required) - Barcode of the image.
  * size (optional) - s/m/l or no value. Retuns urls accordingly

Note that either image\_id or barcode is required but not both.

## Output Formats ##

If the api request is successful, then the response will have a MIME type text/plain. If an error occurs, a JSON response indicating the error will be returned.

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.


---

## Example Requests ##

1. This example request returns the url of an image.

```
 http://{path_to_software}/api/api.php?cmd=imageGetUrl&imageId=18&size=m
```

> Response:
```
http://{path}/picture_m.jpg
```