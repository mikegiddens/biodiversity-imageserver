## List Sets ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=listSets
```

No URL parameters are required along with this API request.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * total\_count (int) - Number of sets listed.
    * data (object) - Array of item objects.

  * data (object)
    * id (int) - Id of the set.
    * name (string) - Name of the set.
    * description (string) - Description of the set.
    * values (object) - Array of item objects.

  * values (object)
    * id (int) - Id of set value.
    * value (string) - Name of set value.
    * rank (int) - Rank of set value.


---

## Example Requests ##

1. This example request list all sets.

```
 http://{path_to_software}/api/api.php?cmd=listSets
```

> Response:
```
{
    "success": true,
    "processTime": 0.0011079311,
    "total_count": 1,
    "data": [
        {
            "id": 1,
            "name": "{name}",
            "description": "{description}",
            "values": [
                {
                    "id": 4,
                    "value": "{value}",
                    "rank": 1
                }
            ]
        }
    ]
}
```