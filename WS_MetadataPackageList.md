## List MetaData Package ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=metadataPackageList
```

No url parameters is required with this command

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * totalCount (int) - Total number of data rows imported.


---

## Example Requests ##

1. This example request creates a new image attribute.

```
 http://{path_to_software}/api/api.php?cmd=metadataPackageList
```

> Response:
```
{
    "success":true,
    "processTime":0.00075197219848633,
	"totalCount": 2,
    "records": [
        "dublincore.csv",
        "dwc_occurrence.csv"
    ]
}
```