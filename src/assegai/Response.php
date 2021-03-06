<?php

/**
 * Response object for Assegai.
 *
 * This file is part of Assegai
 *
 * Copyright (c) 2013 Guillaume Pasquet
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace assegai;

class Response
{
    /** HTTP headers array. */
	protected $headers;
    /** HTTP status code as integer. */
	protected $status_code;
    /** HTTP response's body string. */
	protected $body;
    /** Content type header. */
	protected $content_type;

    protected $sessionvars;
    protected $cookievars;
    protected $alteredsession = false;
    protected $alteredcookies = false;

    /**
     * Backwards-compatible constructor, for people who have not
     * migrated to the DI yet.
     */
    function __construct($body = '', $status_code = 200,
        $content_type = 'text/html; charset=UTF-8',
        array $cookies = null, array $session = null)
    {
        if(!$session) {
            // PHP 5.4+ first.
            if((function_exists('session_status')
                    && session_status() == PHP_SESSION_ACTIVE)
                || isset($_SESSION)) {
                $session = $_SESSION;
            }
            else if(session_id() && isset($_SESSION)) {
                $session = $_SESSION;
            }
            else {
                $session = array();
            }
        } else {
            $this->sessionvars = $session ?: array();
        }

        if(!$cookies) {
            $cookies = $_COOKIE;
        } else {
            $this->cookievars = $cookies ?: array();
        }

		$this->status_code = $status_code;
		$this->content_type = $content_type;
		$this->headers = array();
		$this->body = $body;
    }
    
	/**
	 * Appends a string to body. Works just like sprintf.
     * @param string $section will be appended to body.
	 */
	public function append($section)
	{
		$this->body .= call_user_func_array('sprintf', func_get_args());
		return $this;
	}

    /**
     * Redirects to a different page.
     * @param string $url is the url to redirect to.
     */
    public function redirect($url)
    {
        $this->setStatus(301);
        $this->setHeader('Location', $url);
        return $this;
    }

	/**
	 * Sets the contents of the HTTP response's body.
     * @param string $data replaces body's contents.
	 */
	public function setBody($data)
	{
		$this->body = $data;
		return $this;
	}

    /**
     * Retrieves the body.
     * @return string body
     */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Sets a header value.
     * @param string $name is the header variable's name.
     * @param mixed $value is the value to assign to $name.
	 */
	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;
		return $this;
	}

    /**
     * Retrieves a header's value.
     * @param string $name is the header's name.
     * @return mixed the header variable's value or FALSE if not found.
     */
	public function getHeader($name)
	{
		if(isset($this->headers[$name])) {
			return $this->headers[$name];
		} else {
			return false;
		}
	}

    /**
     * Returns an array containing all headers.
     * @return array headers.
     */
	public function getHeaders()
	{
		return $this->headers;
	}

    /**
     * Sets a SESSION variable.
     * @param varname is the variable's name.
     * @param varval is the value to assign to the variable.
     * @return FALSE if session isn't started.
     */
    public function setSession($varname, $varval)
    {
        $this->alteredsession = true;
        $this->sessionvars[$varname] = $varval;
        return $this;
    }

    /**
     * Retrieves the value of a session variable.
     * @param $varname is the variable's name
     * @param $default is the default value to be returned.
     * @return the session variable or FALSE if it can't be retrieved.
     */
    public function getSession($varname, $default = false)
    {
        if(isset($this->sessionvars[$varname])) {
            return $this->sessionvars[$varname];
        } else {
            return $default;
        }
    }

    /**
     * Clears a session variable.
     * @param $varname is the session variable's name.
     */
    public function killSession($varname)
    {
        $this->alteredsession = true;
        unset($this->sessionvars[$varname]);
        return $this;
    }

    public function alteredSession() {
        return $this->alteredsession;
    }

    public function alteredCookies() {
        return $this->alteredcookies;
    }

    /**
     * Clears a cookie variable.
     * @param $varname is the cookie variable's name.
     */
    public function killCookie($varname)
    {
        $this->alteredcookies = true;
        unset($this->cookievars[$varname]);
        return $this;
    }

    /**
     * Sets a COOKIE variable.
     * @param varname is the variable's name.
     * @param varval is the value to assign to the variable.
     */
    public function setCookie($varname, $varval)
    {
        $this->alteredcookies = true;
        $this->cookievars[$varname] = $varval;
    }

    /**
     * Retrieves the value of a cookie variable.
     * @param $varname is the variable's name
     * @param $default is the default value to be returned.
     */
    public function getCookie($varname, $default = false)
    {
        if(isset($this->cookievars[$varname])) {
            return $this->cookievars[$varname];
        } else {
            return $default;
        }
    }

    /**
     * Sets the HTTP status code.
     * @param int $statuscode is the HTTP status code to be returned.
     */
    public function setStatus($statuscode)
    {
        if($this->httpStatus($statuscode)) {
            $this->status_code = $statuscode;
        }
        return $this;
    }

    /**
     * Gets all cookies.
     */
    function getAllCookies() {
        return $this->cookievars;
    }

    /**
     * Gets all session.
     */
    function getAllSession() {
        return $this->sessionvars;
    }

    /**
     * Gets the current HTTP status code.
     */
    public function getStatus()
    {
        return $this->status_code;
    }

    /**
     * Fetches the HTTP full status as string from the current integer status.
     * @param int $statuscode is the numeric status code to fetch.
     */
	protected function httpStatus($statuscode)
	{
		$statuses = array(
			100 => '100 Continue',
			101 => '101 Switching Protocols',
			200 => '200 OK',
			201 => '201 Created',
			202 => '202 Accepted',
			203 => '203 Non-Authoritative Information',
			204 => '204 No Content',
			205 => '205 Reset Content',
			206 => '206 Partial Content',
			300 => '300 Multiple Choices',
			301 => '301 Moved Permanently',
			302 => '302 Found',
			303 => '303 See Other',
			304 => '304 Not Modified',
			305 => '305 Use Proxy',
			306 => '306 (Unused)',
			307 => '307 Temporary Redirect',
			400 => '400 Bad Request',
			401 => '401 Unauthorized',
			402 => '402 Payment Required',
			403 => '403 Forbidden',
			404 => '404 Not Found',
			405 => '405 Method Not Allowed',
			406 => '406 Not Acceptable',
			407 => '407 Proxy Authentication Required',
			408 => '408 Request Timeout',
			409 => '409 Conflict',
			410 => '410 Gone',
			411 => '411 Length Required',
			412 => '412 Precondition Failed',
			413 => '413 Request Entity Too Large',
			414 => '414 Request-URI Too Long',
			415 => '415 Unsupported Media Type',
			416 => '416 Requested Range Not Satisfiable',
			417 => '417 Expectation Failed',
			500 => '500 Internal Server Error',
			501 => '501 Not Implemented',
			502 => '502 Bad Gateway',
			503 => '503 Service Unavailable',
			504 => '504 Gateway Timeout',
			505 => '505 HTTP Version Not Supported'
			);

        if(array_key_exists($statuscode, $statuses)) {
            return $statuses[$statuscode];
        } else {
            return false;
        }
	}

	/**
	 * Generates the page.
	 */
	public function compile()
	{
        $session_started = false;
        if(function_exists('session_status') && session_status() == PHP_SESSION_ACTIVE) {
          $session_started = true;
        }
        else if(isset($_SESSION)) {
          $session_started = true;
        }

        if($session_started) {
          // Session handling.
          if(!is_array($this->sessionvars)) {
            $this->sessionvars = array();
          }
          $_SESSION = @array_merge($_SESSION, $this->sessionvars);
        }

		header('HTTP/1.1 ' . $this->httpStatus($this->getStatus()));
		header('Content-Type: ' . $this->content_type);
		foreach($this->headers as $hdrkey => $hdrval) {
			header($hdrkey . ': ' . $hdrval);
		}

        $_COOKIE = @array_merge($_COOKIE, $this->cookievars);

		echo $this->body;
	}
}

?>
