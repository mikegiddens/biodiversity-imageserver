## List Categories ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=categoryList&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

  * start (optional) -
  * limit (optional) -
  * searchFormat (optional) - In what format to search.  exact, left, right, both. default both.
  * value (optional) - search value.
  * categoryId (optional) - Id of the needed category.


## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * totalCount (int) - Total records.
    * records (object) - Array of item objects.

  * record (object)
    * categoryId
    * title
    * description
    * elementSet
    * term




---

## Example Requests ##

1. This example request list all categories.

```
 http://{path_to_software}/api/api.php?cmd=categoryList
```

> Response:
```
{
    "success": true,
    "processTime": 0.0012769699,
    "totalCount": 18,
    "records": [
        {
            "catgoryId": 2,
            "title": "{title}",
            "description": "{description}",
            "elementSet": "{elementSet}",
            "term": {"term}"
        }
    ]
}
```