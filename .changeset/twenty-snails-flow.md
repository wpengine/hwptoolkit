---
"@wpengine/hwp-previews-wordpress-plugin": patch
---

1. Disables Faust front-end redirects for preview url's to solve the iframe conflict.
2. Introduced methods in Faust_Integration to replace Faust-generated preview URLs with the site’s home URL as needed.
