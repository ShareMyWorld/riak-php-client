<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache License, Version 2.0 that is
 * bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Basho\Riak;

/**
 * Provides a specialized CURL write function that handles streaming of keys
 *
 * @author  Andreas Duchen <andreas.duchen@gmail.com>
 */
class StreamKeysIO
{
    
    private $buffer = '';
    
    /**
     * Construct a StreamKeysIO object.
     */
    public function __construct()
    {
        $this->contents = array();
    }

    /**
     * Add and parse keys
     *
     * @param resource $ch Curl Resource Handler (unused)
     * @param string $data Data to add to contents
     *
     * @return int
     */
    public function write($ch, $data)
    {
        $this->buffer .= $data;
        
        // Lets check if we got a complete JSON chunk, otherwise lets wait for next callback
        $parsed_json = json_decode($this->buffer, TRUE);
        if ($parsed_json !== NULL) {
            if (is_array($parsed_json) && isset($parsed_json['keys'])) {
                // We could do array_merge but testing shows that this is just as fast and uses less memory.
                foreach ($parsed_json['keys'] as $key) {
                    $this->contents[] = $key;
                }
            } 
            
            $this->buffer = '';
        }
        
        return strlen($data);
    }

    /**
     * Retrieve all keys as an array
     *
     * @return array
     */
    public function contents()
    {
        return $this->contents;
    }
}