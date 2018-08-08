<?php

    /**
     * IpStack
     * 
     * @link    https://github.com/onassar/PHP-IpStack
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class IpStack
    {
        /**
         * _base
         * 
         * @var     string (default: 'http://api.ipstack.com')
         * @access  protected
         */
        protected $_base = 'http://api.ipstack.com';

        /**
         * _ip
         * 
         * @var     null|string (default: null)
         * @access  protected
         */
        protected $_ip = null;

        /**
         * _key
         * 
         * @var     null|string (default: null)
         * @access  protected
         */
        protected $_key = null;

        /**
         * _cache
         * 
         * @var     array (default: array())
         * @access  protected
         */
        protected $_cache = array();

        /**
         * __construct
         * 
         * @access  public
         * @param   string $key
         * @return  void
         */
        public function __construct($key)
        {
            $this->_key = $key;
        }

        /**
         * _getIp
         * 
         * @throws  Exception
         * @access  protected
         * @return  string
         */
        protected function _getIp()
        {
            if (is_null($this->_ip) === false) {
                return $this->_ip;
            }
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) === true) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if (isset($_SERVER['REMOTE_ADDR']) === true) {
                return $_SERVER['REMOTE_ADDR'];
            }
            $msg = 'No ip found';
            throw new Exception($msg);
        }

        /**
         * _getRecord
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRecord()
        {
            $ip = $this->_getIp();
            if (isset($this->_cache[$ip]) === true) {
                return $this->_cache[$ip];
            }
            $record = $this->_requestRecord();
            $this->_cache[$ip] = $record;
            return $record;
        }

        /**
         * _setRecord
         * 
         * @access  protected
         * @param   array $record
         * @return  array
         */
        protected function _setRecord(array $record)
        {
            $ip = $this->_getIp();
            $this->_cache[$ip] = $record;
        }

        /**
         * _requestRecord
         * 
         * @access  protected
         * @return  array
         */
        protected function _requestRecord()
        {
            $ip = $this->_getIp();
            $key = $this->_key;
            $url = ($this->_base) . '/' . ($ip) . '?access_key=' . ($key);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($json, true);
            return $response;
        }

        /**
         * getCity
         * 
         * @access  public
         * @return  false|string
         */
        public function getCity()
        {
            $record = $this->_getRecord();
            if (isset($record['city']) === false) {
                return false;
            }
            return utf8_encode($record['city']);
        }

        /**
         * getCountry
         * 
         * @access  public
         * @return  false|string
         */
        public function getCountry()
        {
            $record = $this->_getRecord();
            if (isset($record['country_name']) === false) {
                return false;
            }
            return utf8_encode($record['country_name']);
        }

        /**
         * getCountryCode
         * 
         * @access  public
         * @return  false|string
         */
        public function getCountryCode()
        {
            $record = $this->_getRecord();
            if (isset($record['country_code']) === false) {
                return false;
            }
            return utf8_encode($record['country_code']);
        }

        /**
         * getFormatted
         * 
         * Returns a formatted string for UI presentation. Examples include:
         * - Toronto, Ontatio
         * - London, England
         * - Egypt
         * - Miami, Florida
         * 
         * @access  public
         * @return  string
         */
        public function getFormatted()
        {
            $pieces = array();
            $city = $this->getCity();
            $region = $this->getRegion();
            $country = $this->getCountry();
            $countryCode = $this->getCountryCode();
            $countryCode = strtoupper($countryCode);
            if ($countryCode === 'CA' || $countryCode === 'US') {
                if ($city !== false && $city !== '') {
                    array_push($pieces, $city);
                    if ($region !== false && $region !== '') {
                        array_push($pieces, $region);
                        return implode(', ', $pieces);
                    }
                    if ($country !== false && $country !== '') {
                        array_push($pieces, $country);
                        return implode(', ', $pieces);
                    }
                    array_push($pieces, $countryCode);
                    return implode(', ', $pieces);
                }
                if ($region !== false && $region !== '') {
                    array_push($pieces, $region);
                    if ($country !== false && $country !== '') {
                        array_push($pieces, $country);
                        return implode(', ', $pieces);
                    }
                    array_push($pieces, $countryCode);
                    return implode(', ', $pieces);
                }
                if ($country !== false && $country !== '') {
                    return $country;
                }
                return $countryCode;
            }
            if ($city !== false && $city !== '') {
                array_push($pieces, $city);
                if ($country !== false && $country !== '') {
                    array_push($pieces, $country);
                    return implode(', ', $pieces);
                }
                if ($countryCode !== false && $countryCode !== '') {
                    array_push($pieces, $countryCode);
                    return implode(', ', $pieces);
                }
                return $city;
            }
            if ($country !== false && $country !== '') {
                return $country;
            }
            return '';
        }

        /**
         * getRecord
         * 
         * @access  public
         * @return  array
         */
        public function getRecord()
        {
            $record = $this->_getRecord();
            return $record;
        }

        /**
         * getRegion
         * 
         * @access  public
         * @return  false|string
         */
        public function getRegion()
        {
            $record = $this->_getRecord();
            if (isset($record['region_name']) === false) {
                return false;
            }
            return utf8_encode($record['region_name']);
        }

        /**
         * setIp
         * 
         * @access  public
         * @param   string $ip
         * @return  void
         */
        public function setIp($ip)
        {
            $this->_ip = $ip;
        }

        /**
         * setRecord
         * 
         * @access  public
         * @param   array $record
         * @return  void
         */
        public function setRecord(array $record)
        {
            $this->_setRecord($record);
        }
    }
