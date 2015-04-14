## Move Existing Image ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=imageMoveExisting&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * imageId (required) - Id of the image to be moved.
  * newStorageId (required) - Id of the new storage device to be used.
  * newImagePath (required) - New path to be used for storage.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicates by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.


---

## Example Requests ##

1. This example request moves an image from one storage device to another.

```
 http://{path_to_software}/api/api.php?cmd=imageMoveExisting&imageId=18&newStorageId=2&newImagePath=/{path}
```

> Response:
```
{
    "success": true,
    "processTime": 0.09539726567688,
}
```