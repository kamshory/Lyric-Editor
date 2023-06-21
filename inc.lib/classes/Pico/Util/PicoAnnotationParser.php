<?php

namespace Pico\Util;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class PicoAnnotationParser
{
    /**
     * Raw docblock
     * @var string
     */
    private $rawDocBlock;

    /**
     * Parameters
     * @var array
     */
    private $parameters;

    /**
     * Key pattern
     *
     * @var string
     */
    private $keyPattern = "[A-z0-9\_\-]+";

    /**
     * End pattern
     *
     * @var string
     */
    private $endPattern = "[ ]*(?:@|\r\n|\n)";

    /**
     * Parsed
     *
     * @var boolean
     */
    private $parsedAll = false;

    /**
     * Reflection
     * @var ReflectionClass|ReflectionMethod|ReflectionProperty
     */

    private $reflection;

    /**
     * Constructor
     */
    public function __construct()
    {
        $arguments = func_get_args();
        $count = count($arguments);

        // get reflection from class or class/method
        // (depends on constructor arguments)
        if ($count === 0) {
            throw new \Exception("No zero argument constructor allowed");
        } else if ($count === 1) {
            $reflection = new ReflectionClass($arguments[0]);
        } else {
            $type = $count === 3 ? $arguments[2] : "method";

            if ($type === "method") {
                $reflection = new ReflectionMethod($arguments[0], $arguments[1]);
            } else if ($type === "property") {
                $reflection = new ReflectionProperty($arguments[0], $arguments[1]);
            }
        }
        $this->reflection = $reflection;
        $this->rawDocBlock = $reflection->getDocComment();
        $this->parameters = array();
    }

    /**
     * Get properties
     * 
     * @return array
     */
    public function getProperties()
    {
        return $this->reflection->getProperties();
    }

    private function parseSingle($key)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        } else {
            if (preg_match("/@" . preg_quote($key) . $this->endPattern . "/", $this->rawDocBlock, $match)) {
                return TRUE;
            } else {
                preg_match_all("/@" . preg_quote($key) . "(.*)" . $this->endPattern . "/U", $this->rawDocBlock, $matches);
                $size = sizeof($matches[1]);

                // not found
                if ($size === 0) {
                    return NULL;
                }
                // found one, save as scalar
                elseif ($size === 1) {
                    return $this->parseValue($matches[1][0]);
                }
                // found many, save as array
                else {
                    $this->parameters[$key] = array();
                    foreach ($matches[1] as $elem) {
                        $this->parameters[$key][] = $this->parseValue($elem);
                    }
                    return $this->parameters[$key];
                }
            }
        }
    }

    private function parse()
    {
        $pattern = "/@(?=(.*)" . $this->endPattern . ")/U";

        preg_match_all($pattern, $this->rawDocBlock, $matches);

        foreach ($matches[1] as $rawParameter) {
            if (preg_match("/^(" . $this->keyPattern . ")(.*)$/", $rawParameter, $match)) {
                $parsedValue = $this->parseValue($match[2]);
                if (isset($this->parameters[$match[1]])) {
                    $this->parameters[$match[1]] = array_merge((array)$this->parameters[$match[1]], (array)$parsedValue);
                } else {
                    $this->parameters[$match[1]] = $parsedValue;
                }
            } else if (preg_match("/^" . $this->keyPattern . "$/", $rawParameter, $match)) {
                $this->parameters[$rawParameter] = TRUE;
            } else {
                $this->parameters[$rawParameter] = NULL;
            }
        }
    }

    public function getVariableDeclarations($name)
    {
        $declarations = (array)$this->getParameter($name);
        foreach ($declarations as &$declaration) {
            $declaration = $this->parseVariableDeclaration($declaration, $name);
        }
        return $declarations;
    }

    private function parseVariableDeclaration($declaration, $name)
    {
        $type = gettype($declaration);

        if ($type !== 'string') {
            throw new \InvalidArgumentException(
                "Raw declaration must be string, $type given. Key='$name'."
            );
        }

        if (strlen($declaration) === 0) {
            throw new \InvalidArgumentException(
                "Raw declaration cannot have zero length. Key='$name'."
            );
        }

        $declaration = explode(" ", $declaration);
        if (sizeof($declaration) == 1) {
            // string is default type
            array_unshift($declaration, "string");
        }

        // take first two as type and name
        $declaration = array(
            'type' => $declaration[0],
            'name' => $declaration[1]
        );

        return $declaration;
    }

    private function parseValue($originalValue)
    {
        if ($originalValue && $originalValue !== 'null') {
            // try to json decode, if cannot then store as string
            if (($json = json_decode($originalValue, true)) === null) {
                $value = $originalValue;
            } else {
                $value = $json;
            }
        } else {
            $value = null;
        }
        return $value;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        if (!$this->parsedAll) {
            $this->parse();
            $this->parsedAll = TRUE;
        }
        return $this->parameters;
    }

    public function getParameter($key)
    {
        return $this->parseSingle($key);
    }

    public function parseKeyValue($queryString)
    {
        // Please test with https://regex101.com/
        
        // parse with quotes
        $regex1 = '/([_\-\w+]+)\=\"([a-zA-Z0-9\-\+ _,.\(\)\{\}\`\~\!\@\#\$\%\^\*\\\|\<\>\[\]\/&%?=:;\'\t\r\n|\r|\n]+)\"/m';
        preg_match_all($regex1, $queryString, $matches);
        $pair1 = array_combine($matches[1], $matches[2]);
        
        // parse without quotes
        $regex2 = '/([_\-\w+]+)\=([a-zA-Z0-9._]+)/m';
        preg_match_all($regex2, $queryString, $matches);
        $pair2 = array_combine($matches[1], $matches[2]);
        
        // merge result
        return array_merge($pair1, $pair2);
    }
}
