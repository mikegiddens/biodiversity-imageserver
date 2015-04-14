## Add Image FROM URL ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=imageAddFromUrl&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

Allowed Image Types: JPG, PNG, GIF

The API defines a request using the following URL parameters:

  * url (required) — This is the URL of the images on the internet.
  * storageDeviceId (required) - This is id of the device where the image will be stored.
  * imagePath (optional) - This is the path on where the images will be stored on the storage device.
  * key (required) - This is the key needed to allow the user to upload iamges from the associated ip address.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicates by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * image\_id (int) - Id of the image.


---

## Example Requests ##

1. This example request add image from URL.

```
 http://{path_to_software}/api/api.php?cmd=imageAddFromUrl&url=http://www.gstatic.com/codesite/ph/images/defaultlogo.png&storageDeviceId=1&imagePath=/mypics&key={yourkey}
```

> Response:
```
{
    "success": true,
    "processTime": 0.07549786567688,
    "imageId" : 98
}
```