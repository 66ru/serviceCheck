<?php

namespace m8rge\OCF;

/**
 * Check any service availability
 *
 * This resource will search for successful regex match against data fetched from pointed url.
 */
class ServiceCheck extends OCF
{
    /**
     * Fetching url
     *
     * Url must point to permanent ip address (ex.: http://127.0.0.1/)
     * @var string
     */
    public $url;

    /**
     * Host header value
     *
     * This "Host: " header will passed with get request
     * @var string
     */
    public $hostName;

    /**
     * Matching regex
     *
     * Success regex match will trigger writing score to score attribute. Failed match will write 0 to score attribute.
     * @var string
     */
    public $regex = '/<html>.*?</html>/';

    /**
     * Attribute name
     *
     * The name of the attributes to set.  This is the name to be used in the constraints.
     * @var string
     */
    public $scoreAttr = 'fe-score';

    /**
     * Success regex match score
     *
     * This number will written to score attribute
     * @var int
     */
    public $score = 1;
}