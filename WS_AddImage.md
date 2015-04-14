## Add Image ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=addImage&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * storage\_id (optional) - This is id of the device where the image will be stored.
  * imagePath (required) - This is the path where the images will be stored on the storage device.
  * key (required) - This is the key needed to allow the user to upload images from the associated ip address.
  * filename (required) - This is the filename for the image.
  * stream (required) - This is the streamed image data.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * image\_id (int) - Id of the new image.


---

## Example Requests ##

1. This example request adds an existing image to database.

```
 http://{path_to_software}/api/api.php?cmd=addImage&storage_id=2&imagePath={/path_to_image}&filename={filename}&key={key}&stream={stream}
```

> Response:
```
{
    "success":true,
    "processTime":0.015973806381226,
    "image_id":100
}
```