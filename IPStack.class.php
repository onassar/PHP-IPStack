<?php

    /**
     * IPStack
     * 
     * @link    https://github.com/onassar/PHP-IPStack
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class IPStack
    {
        /**
         * _base
         * 
         * @access  protected
         * @var     string (default: 'http://api.ipstack.com')
         */
        protected $_base = 'http://api.ipstack.com';

        /**
         * _cache
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_cache = array();

        /**
         * _ip
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_ip = null;

        /**
         * _key
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_key = null;

        /**
         * __construct
         * 
         * @access  public
         * @param   string $key
         * @return  void
         */
        public function __construct(string $key)
        {
            $this->_key = $key;
        }

        /**
         * _format
         * 
         * @access  protected
         * @param   array $record
         * @param   string $key
         * @return  false|string
         */
        protected function _format(array $record, string $key)
        {
            $record = $this->_getRecord();
            if (isset($record[$key]) === false) {
                return false;
            }
            if ($record[$key] === false) {
                return false;
            }
            if ($record[$key] === null) {
                return false;
            }
            return $record[$key];
            // $formatted = utf8_encode($record[$key]);
            // return $formatted;
        }

        /**
         * _getIP
         * 
         * @throws  Exception
         * @access  protected
         * @return  string
         */
        protected function _getIP(): string
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
         * @return  null|array
         */
        protected function _getRecord(): ?array
        {
            $ip = $this->_getIP();
            if (isset($this->_cache[$ip]) === true) {
                return $this->_cache[$ip];
            }
            $record = $this->_requestRecord();
            $this->_cache[$ip] = $record;
            return $record;
        }

        /**
         * _getRequestPath
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestPath(): string
        {
            $ip = $this->_getIP();
            $path = '/' . ($ip);
            return $path;
        }

        /**
         * _getRequestQueryString
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestQueryString(): string
        {
            $key = $this->_key;
            $queryData = array(
                'access_key' => $key
            );
            $queryString = http_build_query($queryData);
            return $queryString;
        }

        /**
         * _getRequestURL
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestURL(): string
        {
            $base = $this->_base;
            $path = $this->_getRequestPath();
            $queryString = $this->_getRequestQueryString();
            $url = ($base) . ($path) . '?' . ($queryString);
            return $url;
        }

        /**
         * _requestRecord
         * 
         * @access  protected
         * @return  null|array
         */
        protected function _requestRecord(): ?array
        {
            $url = $this->_getRequestURL();
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);
            if (is_string($json) === false) {
                return null;
            }
            $response = json_decode($json, true);
            if ($response === null) {
                return null;
            }
            return $response;
        }

        /**
         * _setRecord
         * 
         * @access  protected
         * @param   array $record
         * @return  void
         */
        protected function _setRecord(array $record): void
        {
            $ip = $this->_getIP();
            $this->_cache[$ip] = $record;
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
            $formatted = $this->_format($record, 'city');
            return $formatted;
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
            $formatted = $this->_format($record, 'country_name');
            return $formatted;
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
            $formatted = $this->_format($record, 'country_code');
            return $formatted;
        }

        /**
         * getCountryEmojiCharacter
         * 
         * @access  public
         * @return  false|string
         */
        public function getCountryEmojiCharacter()
        {
            $record = $this->_getRecord();
            $value = $record['location']['country_flag_emoji'] ?? false;
            return $value;
        }

        /**
         * getCountryEmojiImageURL
         * 
         * @access  public
         * @return  false|string
         */
        public function getCountryEmojiImageURL()
        {
            $record = $this->_getRecord();
            $value = $record['location']['country_flag'] ?? false;
            return $value;
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
        public function getFormatted(): string
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
         * @return  null|array
         */
        public function getRecord(): ?array
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
            $formatted = $this->_format($record, 'region_name');
            return $formatted;
        }

        /**
         * setIP
         * 
         * @access  public
         * @param   string $ip
         * @return  void
         */
        public function setIP($ip): void
        {
            $this->_ip = $ip;
        }

        /**
         * setRecord
         * 
         * @note    This exists to allow for middleware caching of data to
         *          prevent unncessary lookups
         * @access  public
         * @param   array $record
         * @return  void
         */
        public function setRecord(array $record): void
        {
            $this->_setRecord($record);
        }
    }
