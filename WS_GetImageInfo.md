## Get Image Info ##

An API request must be of the following form:

```
 http://{url_to_software}/api/api.php?cmd=getImageInfo&parameters
```

Certain parameters are required while some are optional. As is standard in URLs, all parameters are separated using the ampersand (&) character. The list of parameters and their possible values are enumerated below.

The API defines a request using the following URL parameters:

  * image\_id (required) - Id of the image.
  * barcode (required) - Barcode of the image.

Note that you must provide either image\_id or barcode but not both.

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
    * image\_id (int) - Id of the image.
    * filename (string) - Filename of the image.
    * timestamp\_modified (datetime) - Last modified time stamp.
    * barcode (string) - Barcode of the image.
    * width (int) - Width of the image.
    * height (int) - Height of the image.
    * Family (string) - {Family}
    * Genus (string) - {Genus}
    * SpecificEpithet (string) - {SpecificEpithet}
    * rank (int) - {rank}
    * author (string) - {author}
    * title (string) - {title}
    * description (string) - {description}
    * GlobalUniqueIdentifier (string) - {GlobalUniqueIdentifier}
    * creative\_commons (string) - {creative\_commons}
    * characters (text) - {characters}
    * flickr\_PlantID (int) - {flickr\_PlantID}
    * flickr\_modified (datetime) - {flickr\_modified}
    * flickr\_details (string) - {flickr\_details}
    * picassa\_PlantID (int) - {picassa\_PlantID}
    * picassa\_modified (datetime) - {}
    * gTileProcessed (int) - {picassa\_modified}
    * zoomEnabled (int) - {zoomEnabled}
    * processed (int) - {processed}
    * box\_flag (int) - {box\_flag}
    * ocr\_flag (int) - {ocr\_flag}
    * ocr\_value (string) - {ocr\_value}
    * namefinder\_flag (int) - {namefinder\_flag}
    * namefinder\_value (string) - {namefinder\_value}
    * ScientificName (string) - {ScientificName}
    * CollectionCode (string) - {CollectionCode}
    * CatalogueNumber (int) - {CatalogueNumber}
    * guess\_flag (int) - {guess\_flag}
    * tmpFamily (string) - {tmpFamily}
    * tmpFamilyAccepted (string) - {tmpFamilyAccepted}
    * tmpGenus (string) - {tmpGenus}
    * tmpGenusAccepted (string) - {tmpGenusAccepted}
    * storage\_id (int) - Id of the storage device.
    * path (string) - Path of image within storage device.
    * originalFilename (string) - Filename of the image.
    * url (string) - URL of the image.
    * attributes (object) - Array of item objects.
    * events (object) - Array of item objects.

  * attributes (object)
    * id (int) - Id of the attribute type.
    * key (string) - Name of attribute type.
    * values (object) - Array of item objects.

  * values (object)
    * id (int) - Id of attribute value.
    * value (string) - Name of attribute value.

  * events (object)
    * id (int) - Id of the event.
    * name (string) - Name of the event.


---

## Example Requests ##

1. This example request gets the image info.

```
 http://{path_to_software}/api/api.php?cmd=getImageInfo&image_id=50
```

> Response:
```
{
    "success": true,
    "processTime": 0.0028119087,
    "data": {
        "image_id": 50,
        "filename": "picture.jpg",
        "timestamp_modified": "2012-05-28 05:35:30",
        "barcode": "{barcode}",
        "width": 0,
        "height": 0,
        "Family": "{Family}",
        "Genus": "{Genus}",
        "SpecificEpithet": "{SpecificEpithet}",
        "rank": 0,
        "author": "{author}",
        "title": "{title}",
        "description": "{description}",
        "GlobalUniqueIdentifier": "{GlobalUniqueIdentifier}",
        "creative_commons": "{creative_commons}",
        "characters": "{characters}",
        "flickr_PlantID": 0,
        "flickr_modified": "0000-00-00 00:00:00",
        "flickr_details": "{flickr_details}",
        "picassa_PlantID": 0,
        "picassa_modified": "0000-00-00 00:00:00",
        "gTileProcessed": 0,
        "zoomEnabled": 0,
        "processed": 1,
        "box_flag": 1,
        "ocr_flag": 0,
        "ocr_value": "{ocr_value}",
        "namefinder_flag": 0,
        "namefinder_value": "{namefinder_value}",
        "ScientificName": "{ScientificName}",
        "CollectionCode": "{CollectionCode}",
        "CatalogueNumber": 0,
        "guess_flag": 0,
        "tmpFamily": "{tmpFamily}",
        "tmpFamilyAccepted": "{tmpFamilyAccepted}",
        "tmpGenus": "{tmpGenus}",
        "tmpGenusAccepted": "{tmpGenusAccepted}",
        "storage_id": 2,
        "path": "/{path}",
        "originalFilename": "picture.jpg",
        "url": "http://{base_url}/{path}/{filename}",
        "attributes": [
            {
                "id": 2,
                "key": "{key}",
                "values": [
                    {
                        "id": 2,
                        "value": "{value}"
                    }
                ]
            }
        ],
        "events": [
            {
                "id": 3,
                "name": "{name}"
            }
        ]
    }
}
```