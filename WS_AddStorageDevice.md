## Add Storage Device ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=storageDeviceAdd&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * name (required) - Name of the new storage device.
  * type (required) - Type of the new storage device.
  * baseUrl (required) - Base Url of the new storage device.
  * description (optional) - A brief description of the storage device.
  * basePath (optional) - Path within storage device which has to be used for storage.
  * userName (optional) - User name required for authentication.
  * password (optional) - Password required for authentication.
  * key (optional) - Key required for authentication.
  * active (optional) - Specifies whether device can be used or not. Default true.
  * default (optional) - Specifies whether the storage device should be the default one. Default false.
  * extra2 (reserved) - Reserved for future use.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * storageDeviceId (int) - Db id of the new storage device.


---

## Example Requests ##

1. This example request creates a new storage device.

```
 http://{path_to_software}/api/api.php?cmd=storageDeviceAdd&name=storageDeviceAdd&type=local&baseUrl={url_to_storage_device}
```

> Response:
```
{
    "success":true,
    "processTime":0.0085549354553223,
    "storageDeviceId":3
}
```