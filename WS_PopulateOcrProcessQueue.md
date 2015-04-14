## Populate Ocr Process Queue ##

An API request must be of the following form:

```
 http://{url_to_software}/api/backup_services.php?cmd=populateOcrProcessQueue&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * limit (optional) - Number of items to be added to queue.
  * imageId (optional) - JSON array containing image ids to be populated.

> Note that if no parameters are provided, all images with ocr\_flag set to 0 will be added to queue.

## Output Formats ##

  * [json](#JSON_Output_Formats.md) - Available


---

## Responses ##

Responses are returned in the format indicated by the output flag within the URL request's path.

> ### JSON Output Formats ###
    * success (bool) - If response was successful or not. If it false see [JSON Error Response](http://code.google.com/p/biodiversity-imageserver/wiki/jsonErrorResponse) for more details
    * processTime (float) - Time it takes to complete the request.
    * totalCount (int) - Number of records added.


---

## Example Requests ##

1. This example request adds all images to OCR process queue.

```
 http://{path_to_software}/api/api.php?cmd=populateOcrProcessQueue
```

> Response:
```
{
    "success":true,
    "processTime":0.3475089073181,
    "totalCount":525
}
```