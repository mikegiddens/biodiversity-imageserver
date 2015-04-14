## Add Existing Image ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=imageAddFromExisting&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * storageDeviceId (required) - The storage device where image exists.
  * imagePath (required) - The path to the image.
  * filename (required) - The filename for the image.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * imageId (int) - Id of the image.


---

## Example Requests ##

1. This example request adds an existing image to database.

```
 http://{path_to_software}/api/api.php?cmd=imageAddFromExisting&storageDeviceId=2&imagePath={/path_to_image}&filename={filename}
```

> Response:
```
{
    "success":true,
    "processTime":0.015973806381226,
    "imageId":97
}
```