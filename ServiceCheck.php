<?php

namespace m8rge\OCF;

/**
 * Check any service availability
 *
 * This resource will search for successful regex match against data fetched from pointed url.
 */
class ServiceCheck extends OCF
{
    protected $version = '0.5';

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
    public $scoreAttr = 'srv-score';

    /**
     * Success regex match score
     *
     * This number will written to score attribute
     * @var int
     */
    public $score = 1;

    public function validateProperties()
    {
        $res = parent::validateProperties();
        if ($res) {
            if (!is_numeric($this->score)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @timeout 1
     * @return int
     */
    public function actionStart()
    {
        return self::OCF_SUCCESS;
    }

    /**
     * @timeout 1
     * @return int
     */
    public function actionStop()
    {
        return $this->removeAttribute($this->scoreAttr);
    }

    /**
     * @timeout 10
     * @interval 10
     * @return int
     */
    public function actionMonitor()
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $this->url,
                CURLOPT_HTTPHEADER => ['Host: ' . $this->hostName],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => true,
            ]
        );
        $data = curl_exec($ch);
        if ($data === false) {
            return $this->setAttribute($this->scoreAttr, 0);
        }
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpStatus != 200) {
            return $this->setAttribute($this->scoreAttr, 0);
        }
        if (!preg_match($this->regex, $data)) {
            return $this->setAttribute($this->scoreAttr, 0);
        }
        return $this->setAttribute($this->scoreAttr, $this->score);
    }
}