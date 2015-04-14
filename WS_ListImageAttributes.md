## List Image Attributes ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=imageListAttribute&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * imageId (required) - Id of the image.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * data (object) - Array of item objects.

  * data (object)
    * id (int) - Id of the category.
    * key (string) - Name of the category.
    * values (object) - Array of item objects.

  * values (object)
    * id (int) - Id of the value.
    * value (string) - {value}.


---

## Example Requests ##

1. This example request list attributes of an image.

```
 http://{path_to_software}/api/api.php?cmd=imageListAttribute&imageId=50
```

> Response:
```
{
    "success": true,
    "processTime": 0.0015499592,
    "results": [
        {
            "id": 2,
            "key": "{category}",
            "values": [
                {
                    "id": 2,
                    "value": "{value}"
                }
            ]
        }
    ]
}
```