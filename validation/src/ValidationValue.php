<?php

namespace Az\Validation;

use Psr\Http\Message\UploadedFileInterface;
use stdClass;

final class ValidationValue
{
    use SantizeParams;

    private Parser $parser;
    private Resolver $resolver;
    private Validation $validation;
    public $rules = [];
    public $e;
    public array $uploaded = [];

    public function __construct(Parser $parser, Resolver $resolver, Validation $validation)
    {       
        $this->parser = $parser;
        $this->resolver = $resolver;
        $this->validation = $validation;
    }

    public function rule($handler, ...$params)
    {
        $this->rules = array_merge($this->rules, $this->parser->parse($handler, $params));
        return $this;
    }

    public function check($value)
    {
        $validate = function ($value) {
            foreach ($this->rules as $rule) {
                $params = $this->santizeParams($rule->params, $value);
                $handler = $this->resolver->resolve($rule->handler);
                $result = call_user_func_array($handler, $params);
                $result = ($rule->inverse) ? !$result : $result;
                   
                if ($result !== true) {
                    $this->e = new stdClass;
                    $this->e->handler = $handler;
                    $this->e->params = $params;
                    $this->e->inverse = $rule->inverse;
                    if (is_string($result)) {
                        $this->e->key = $result;
                    }

                    break;
                } elseif ($value instanceof UploadedFileInterface) {
                    $this->uploaded[] = $value;
                }     
            }
    
            return $result;
        };

        if (is_array($value)) {
            $result = true;
            foreach ($value as $val) {
                $res = $validate($val);
                if ($res !== true) {
                    $result = $res;
                    break;
                }
            }
        } else {
            $result = $validate($value);
        }
        
        return $result;
    }
}
