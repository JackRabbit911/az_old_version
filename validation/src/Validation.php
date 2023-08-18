<?php

namespace Az\Validation;

final class Validation
{
    use ModifyData;

    private Response $response;
    private Parser $parser;
    private Resolver $resolver;
    private array $files = [];
    private array $result = [];
    private array $validator = [];
    public array $data = [];

    public function __construct(Response $response, Parser $parser, Resolver $resolver)
    {
        $this->response = $response;
        $this->parser = $parser;
        $this->resolver = $resolver;
    }

    public function rule($name, $handler, ...$params)
    {
        $v = $this->factory($name);
        $v->rule($handler, ...$params);
        return $this;
    }

    public function check($data, $files = [])
    {
        $checkData = $this->checkData($data + $files);
        return $checkData;
    }

    public function getResponse()
    {
        return $this->response->get($this->validator, $this->data, $this->files);
    }

    public function setMsgKey($name, $key)
    {
        $this->response->setMsgKey($name, $key);
        return $this;
    }

    public function addMsgPath($path)
    {
        $this->response->addMsgPath($path);
        return $this;
    }

    public function setMessage($name, $msg)
    {
        $this->response->setMessage($name, $msg);
    }

    public function setLang($lang)
    {
        $this->response->setLang($lang);
        return $this;
    }

    private function checkData(array $data): bool
    {
        $result = true;

        foreach ($this->modifyData($data) as $name => $value) {
            $check = true;

            if (!is_array($value) || array_is_list($value)) {
                if (isset($this->validator[$name])) {
                    $check = $this->validator[$name]->check($value);
                } else {
                    $check = true;
                }
               
                if ($check !== true) {
                    $result = false;
                } else {
                    $this->data[$name] = $value;
                }
            }            
        }

        return $result;
    }

    private function factory($name)
    {
        if (!isset($this->validator[$name])) {
            $this->validator[$name] = new ValidationValue($this->parser, $this->resolver, $this);          
        }

        return $this->validator[$name];
    }
}
