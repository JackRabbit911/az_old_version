<?php

namespace Az\Validation;

use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class ValidationHandler
{
    private array $datatypes = [
        'username'     => ['regexp', '/^[\w\s\-@!.]*$/u'],
        'password'     => ['regexp', '/^[\w\s\-@.]*$/u'],
        'email'        => ['filter', FILTER_VALIDATE_EMAIL],
        'integer'      => ['filter', FILTER_VALIDATE_INT],
        'alpha'        => ['regexp', '/^[a-zA-Z]*$/'],
        'alpha_num'    => ['regexp', '/^[a-zA-Z0-9]*$/'],
        'alpha_utf8'   => ['regexp', '/^[\pL]*$/u'],
        'alpha_num_utf8'=>['regexp', '/^[\w]*$/u'],
        'alpha_space'  => ['regexp', '/^[a-zA-Z\s]*$/u'],
        'alpha_space_utf8'=>['regexp', '/^[\pL\s]*$/u'],
        'text_utf8'    => ['regexp', '/^[^<>]*$/u'],
        'phone'        => ['regexp', '/^[\+\s\d\-()]{3,20}$/'],
        'phone_strict' => ['regexp', '/^[\d]{11,11}$/'],
        // 'dob'          => ['regexp', '/^[\d]{4}[\-]{1}[0-2]{2}[\d]{2}[\-]{1}$/']
        ];

    public function _is_callable($funcName)
    {
        return (isset($this->datatypes[$funcName]) || method_exists($this, $funcName));
    }

    public function __call(string $name, array $arguments)
    {
        if(isset($this->datatypes[$name]))
        {
            $func = $this->datatypes[$name][0];
            array_push($arguments, $this->datatypes[$name][1]);          
            return call_user_func_array([$this, $func], $arguments);
        }
        elseif(ini_get('display_errors') !== 0)
            throw new RuntimeException(sprintf('function: %s not found!', $name));
        else return true;
    }

    public function regexp($value, $regex): bool
    {
        if(empty($value)) return true;
        return (preg_match($regex, $value) === 0) ? false : true;
    }

    public function filter($value, $filter, $options = []): bool
    {
        if(empty($value)) return true;
        return (filter_var($value, $filter, $options) === false) ? false : true;
    }

    public function confirm($value, $data, $field = 'Password'): bool
    {
        return ($value === $data[$field]) ? true : false;
    }

    public function length($value, $min, $max): bool
    {
        if(empty($value)) return true;
        return (mb_strlen($value) < $min || mb_strlen($value) > $max) ? false : true;
    }

    public function minLength($value, $min): bool
    {
        if(empty($value)) return true;
        return (mb_strlen($value) < $min) ? false : true;
    }

    public function maxLength($value, $max): bool
    {
        if(empty($value)) return true;
        return (mb_strlen($value) > $max) ? false : true;
    }

    public function boolean($value): bool
    {
        return is_bool(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
    }

    public function yes($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function maxValue($value, $max): bool
    {
        return ($value > $max) ? false : true;
    }

    public function minValue($value, $min): bool
    {
        return ($value < $min) ? false : true;
    }

    public function required($value): bool
    {
        return ($value === '' || $value === null) ? false : true;
    }

    public function valid_date($value, $format = 'Y-m-d')
    {
        if(empty($value)) return true;

        $d = \DateTime::createFromFormat($format, $value);

        if ($d && $d->format($format) == $value) {
            return true;
        } else {
            return false;
        }
    }

    /***  functions for uploaded files  ***/
    
    public function notEmpty(UploadedFileInterface $upFile)
    {
        return ($upFile->getError() === UPLOAD_ERR_NO_FILE) ? false : true;
    }

    public function size(UploadedFileInterface $upFile, $size)
    {
        if ($upFile->getError() === UPLOAD_ERR_INI_SIZE) {
            return false;
        }
        return ($upFile->getSize() <= $this->human2byte($size));
    }

    public function mime(UploadedFileInterface $upFile, ...$mimes)
    {
        if ($upFile->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        return (in_array($upFile->getClientMediaType(), $mimes)) ? true : false;
    }

    public function ext(UploadedFileInterface $upFile, ...$extensions)
    {
        if ($upFile->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        $ext = pathinfo($upFile->getClientFilename(), PATHINFO_EXTENSION);
        return (in_array(strtolower($ext), $extensions)) ? true : false;
    }

    public function type(UploadedFileInterface $upFile, $type)
    {
        if ($upFile->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        return (strpos($upFile->getClientMediaType(), $type) === 0) ? true : false;
    }

    public function img(UploadedFileInterface $upFile)
    {
        if ($upFile->getError() === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        $ext = pathinfo($upFile->getClientFilename(), PATHINFO_EXTENSION);
        return (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']) 
            && strpos($upFile->getClientMediaType(), 'image') === 0) 
            ? true : false;
    }

    private function human2byte($value) {
        $str = preg_replace_callback('/^\s*(\d+)\s*(?:([kmgt]?)b?)?\s*$/i', function ($m) {
          switch (strtolower($m[2])) {
            case 't': $m[1] *= 1024;
            case 'g': $m[1] *= 1024;
            case 'm': $m[1] *= 1024;
            case 'k': $m[1] *= 1024;
          }
          return $m[1];
        }, $value);

        return (ctype_digit($str)) ? (int) $str : $str;
    }
}
