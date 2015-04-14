## List Attributes ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=list_attributes&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * categoryID (required) - Id of the category.

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
    * valueID (int) - Id of the attribute.
    * name (string) - Name of the attribute.


---

## Example Requests ##

1. This example request list attributes under a category.

```
 http://{path_to_software}/api/api.php?cmd=list_attributes&categoryID=3
```

> Response:
```
{
    "success": true,
    "processTime": 0.00036787986755371,
    "data": [
        {
            "valueID": 4,
            "name": "{attribute}"
        }
    ]
}
```