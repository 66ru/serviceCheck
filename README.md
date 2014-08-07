ServiceCheck
============

ServiceCheck is [OCF compliant resource agent](http://linux-ha.org/wiki/OCF_Resource_Agents).
It checks web service answer with regexp and write result score to [CIB Cluster Information Base](https://www.suse.com/documentation/sle_ha/singlehtml/book_sleha/book_sleha.html#vle.cib) variable.

## Parameters
 - `url`* (string): Fetching url
    Url must point to permanent ip address (ex.: http://127.0.0.1/)

 - `hostName`* (string): Host header value
    This "Host: " header will passed with get request

 - `regex` (string, [`/<html>.*?</html>/`]): Matching regex
    Success regex match will trigger writing score to score attribute

 - `scoreAttr` (string, [`fe-score`]): Attribute name
    The name of the attributes to set.  This is the name to be used in the constraints.

 - `score` (integer, [`1`]): Success regex match score
    This number will written to score attribute

 - `sentryDSN` (string): Sentry dsn url
