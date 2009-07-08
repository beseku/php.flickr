<?php
	/**
	*	@package		Flickr
	*	@author			Ben Sekulowicz-Barclay
	*	@copyright		Copyright 2009, Ben Sekulowicz-Barclay
	*	@version		0.01
	*
	********************************************************************************************************************************************* **/
	
	class Flickr {
		
		private $api_key 				= 'ddc795a03c208075f079d9b694384e3a';
		private $api_uri 				= 'http://api.flickr.com/services/rest/?format=php_serial';
		                            	
		private $set_cache_duration 	= 3600;
		private $set_cache_enabled 		= TRUE;
		private $set_cache_location 	= './cache/flickr/';				
		
		/** 
		*	@access	public
		*	@return	void
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		public function Flickr() {
			
		}
		
		/** 
		*	@access	public
		*	@param	string
		*	@param	array
		*	@return	mixed
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		public function get($method, $params = array()) {
			// Create our URL
			$this->get_url($method, $params);
			
			// If we can find a valid cached result
			if (!($this->result = $this->cache_read())) {
				
				// If we are querying Flickr, but via FILE_GET_CONTENTS
				if (function_exists('file_get_contents')) {
					$this->result = unserialize(file_get_contents($this->set_url));

				// If we are querying Flickr, but via CURL
				} else if (function_exists('curl_init')) {
					$curl = curl_init($this->set_url) ;
					curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1) ;
					$this->result = unserialize(curl_exec($curl));
					curl_close($curl);
				}
				
				// Create our cache file
				$this->cache_write();
			}
			
			// If we got a recognised response ...
			// @todo: Test the array for recognized keys before returning?
			return (is_array($this->result))? $this->result: FALSE;
		}
		
		/** 
		*	@access	public
		*	@return	mixed
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		private function cache_read() {
			// Create our cache handle/filename
			$cached = $this->set_cache_location . md5($this->set_url);
			
			// If caching is disabled or the file does not exist, stop here
			if (($this->set_cache_enabled == FALSE) || (!(file_exists($cached)))) {
				return FALSE;
			}			
			
			// If the file is too old ...
			if ($this->set_cache_duration < (date("U") - date ("U", filemtime($cached)))) {				
				// Remove the old file
				unlink($cached);
				
				// Refresh our Flickr call
				return FALSE;
			}
			
			// Read from our file
			$handle = @fopen($cached, "r");
			$result = @fread($handle, filesize($cached));
			@fclose($handle);
			
			// Return the cached result
			return unserialize($result);
		}
		
		/** 
		*	@access	public
		*	@return	boolean
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		private function cache_write() {
			// If caching is disabled, don't do anything
			if ($this->set_cache_enabled == FALSE) {
				return FALSE;
			}
			
			// Create our cache handle/filename
			$cached = $this->set_cache_location . md5($this->set_url);
			
			// Write our file
			$handle = @fopen($cached, "w");
			@fwrite($handle, serialize($this->result));
			@fclose($handle);	
			
			// For completeness
			return;		
		}
		
		/** 
		*	@access	public
		*	@param	string
		*	@param	array
		*	@return	boolean
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		private function get_url($method, $params) {
			// Define our default URL ...
			$this->set_url = $this->api_uri;
			
			// Remove any 'damaging' options from the user's array
			if (isset($params['format'])) unset($params['format']);
			if (isset($params['method'])) unset($params['method']);
			
			// Merge our arrays, so users can overwrite
			$params = array_merge(array('api_key' => $this->api_key, 'method' => $method), $params);
			
			// Add in our user generated options
			foreach ($params as $name => $value){
				$this->set_url .= '&' . urlencode($name) . '=' . urlencode($value);
			}
			
			// For completeness
			return;
		}	
		
		
		/** 
		*	@access	public
		*	@param	string
		*	@return	void
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		public function set_cache_duration($value = 3600) {
			$this->set_cache_duration = $value;
		}
		
		/** 
		*	@access	public
		*	@param	string
		*	@return	void
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		public function set_cache_enabled($value = TRUE) {
			$this->set_cache_enabled = $value;			
		}
		
		/** 
		*	@access	public
		*	@param	string
		*	@return	void
		*	@author Ben Sekulowicz-Barclay
		*
		***************************************************************************************************************************************** **/		
		
		public function set_cache_location($value = './cache') {
			$this->set_cache_location = $value;			
		}
		
	}
	
	/** ***************************************************************************************************************************************** **/			
?>