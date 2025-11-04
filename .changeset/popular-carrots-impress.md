---
"@wpengine/wpgraphql-logging-wordpress-plugin": patch
---

chore: Various improvement to the logging plugin:

- Implemented BufferHandler to batch write database entries for performance
- Implemented LogStoreService to remove hard dependencies of the Database services in the admin and various classes
- Refactored Database and CRUD log services to use interfaces to make it easy to be exctended for other data storage solutions.
- Added missing indexes for performance
- Added filters for caching configuration
- Better error handling
- Added missing nonce for admin pages

