# resourcespacedownloader
Batch downloader for files and metadata in resourcespace.  Downloads files based on search criteria and associated metadata.  This script is nessesary to make large downloads because currently can only download a few 100 via the website.

The api is pretty bare bones, which is fine. However, it might result in some collating on your end.

Specific issues:
The example code should work, although check that the username is correct. Our username had ..LDAP at the end.
PHP couldn't download the files off of an https link. Switching to http fixed this
Montala's servers can not handle downloads that are too big. Do them in chunks/month. 
Metadata only can also be downloaded by using the apimetadata.php file.

General notes:
On the server, the folder structure is based on the id of the item (stored as ref). Each folder is a digit of the id.
There are probably 3 queries you want to make for each item.
	1) get_resource_path returns the path to the actual item on the server
	2) get_resource_data returns a short version of the metadata
	3) get_resource_field_data returns a long form version of the metadata
Additionally, there is some processing needed to compile the metadata for an item.
The short metadata will contain a bunch of weird items labeled "fieldX", as well as standard file data like creation_time.
The long metadata is almost unreadable. It contains things like whether the data is hidden or not. However, the "title" and "fref" (field reference) fields are very useful.
The "title" of the item with "fref" 12 in the long metadata is the actual name for "field12" in the short metadata, and the value in the short metadata is the value that should be associated with the title.

Documentation on the API here: https://www.resourcespace.com/knowledge-base/api/

Notes from Montala about the process: 
-The API works by making calls to specific URLs in order to get data back from your ResourceSpace installation. A good starting point will be to use the new API Test Tool included in ResourceSpace. If you navigate to Admin menu -> System -> API test tool you can test the various API functions available. I would start with the do_search function, you will only need to input the first parameter, the search term. 
-When you click Submit you will see the response that ResourceSpace will return, and below this you will see the PHP code required to run this. You don't have to use PHP but it is a good place to start to see how the query is hashed and interpreted. 

So use: connect to the server, navigate to the place where you would like the files to live, currently /var/www/html/archon/files/publicaitonsphotos/. Add the apimetadata.php to that location and run it with a search parameter. 

Examples of searches: 'php api.php "date:2018-05"' would download any files with a date containing 2018-05.
'php apimetadata.php "date:2018-05"' would download any metadata for files with a date containing 2018-05.
