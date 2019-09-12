# resourcespacedownloader
Batch downloader for files and metadata in resourcespace.  Downloads files based on search criteria and associated metadata.

The api is pretty bare bones, which is fine. However, it might result in some collating on your end.

Specific issues:
The example code should work, although check that the username is correct. Our username had ..LDAP at the end.
PHP couldn't download the files off of an https link. Switching to http fixed this

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
