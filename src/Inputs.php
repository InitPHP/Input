<?php
/**
 * Inputs.php
 *
 * This file is part of Input.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.2
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Input;

use InitPHP\ParameterBag\ParameterBag;
use InitPHP\Validation\Validation;

class Inputs
{

    /** @var ParameterBag */
    private static $get;

    /** @var ParameterBag */
    private static $post;

    /** @var ParameterBag */
    private static $raw;

    /** @var Validation */
    private static $validation;

    public function __construct()
    {
        $this->getParameterBagBoot();
        $this->postParameterBagBoot();
        $this->rawParameterBagBoot();
        if(!isset(self::$validation)){
            self::$validation = new Validation();
        }
    }

    public function __destruct()
    {
        if(isset(self::$validation)){
            self::$validation->clear();
        }
        if(isset(self::$get)){
            self::$get->close();
        }
        if(isset(self::$post)){
            self::$post->close();
        }
        if(isset(self::$raw)){
            self::$raw->close();
        }
    }


    public function get(string $key, $default = null, ?array $validation = null)
    {
        if(!self::$get->has($key)){
            return $default;
        }
        $data = self::$get->get($key, $default);
        if(empty($validation)){
            return $data;
        }
        return $this->validData(self::$get->all(), $key, $validation) ? $data : $default;
    }

    public function post(string $key, $default = null, ?array $validation = null)
    {
        if(!self::$post->has($key)){
            return $default;
        }
        $data = self::$post->get($key, $default);
        if(empty($validation)){
            return $data;
        }
        return $this->validData(self::$post->all(), $key, $validation) ? $data : $default;
    }

    public function raw(string $key, $default = null, ?array $validation = null)
    {
        if(!self::$raw->has($key)){
            return $default;
        }
        $data = self::$raw->get($key, $default);
        if(empty($validation)){
            return $data;
        }
        return $this->validData(self::$raw->all(), $key, $validation) ? $data : $default;
    }

    public function getPost(string $key, $default = null, ?array $validation = null)
    {
        return self::$get->has($key) ? $this->get($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function getRaw(string $key, $default = null, ?array $validation = null)
    {
        return self::$get->has($key) ? $this->get($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function getPostRaw(string $key, $default = null, ?array $validation = null)
    {
        if(self::$get->has($key)){
            return $this->get($key, $default, $validation);
        }
        return self::$post->has($key) ? $this->post($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function getRawPost(string $key, $default = null, ?array $validation = null)
    {
        if(self::$get->has($key)){
            return $this->get($key, $default, $validation);
        }
        return self::$raw->has($key) ? $this->raw($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function postGet(string $key, $default = null, ?array $validation = null)
    {
        return self::$post->has($key) ? $this->post($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function postRaw(string $key, $default = null, ?array $validation = null)
    {
        return self::$post->has($key) ? $this->post($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function postGetRaw(string $key, $default = null, ?array $validation = null)
    {
        if(self::$post->has($key)){
            return $this->post($key, $default, $validation);
        }
        return self::$get->has($key) ? $this->get($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function postRawGet(string $key, $default = null, ?array $validation = null)
    {
        if(self::$post->has($key)){
            return $this->post($key, $default, $validation);
        }
        return self::$raw->has($key) ? $this->raw($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function rawGet(string $key, $default = null, ?array $validation = null)
    {
        return self::$raw->has($key) ? $this->raw($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function rawPost(string $key, $default = null, ?array $validation = null)
    {
        return self::$raw->has($key) ? $this->raw($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function rawGetPost(string $key, $default = null, ?array $validation = null)
    {
        if(self::$raw->has($key)){
            return $this->raw($key, $default, $validation);
        }
        return self::$get->has($key) ? $this->get($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function rawPostGet(string $key, $default = null, ?array $validation = null)
    {
        if(self::$raw->has($key)){
            return $this->raw($key, $default, $validation);
        }
        return self::$post->has($key) ? $this->post($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function hasGet(string $key): bool
    {
        return self::$get->has($key);
    }

    public function hasPost(string $key): bool
    {
        return self::$post->has($key);
    }

    public function hasRaw(string $key): bool
    {
        return self::$raw->has($key);
    }

    private function validData(array $data, string $key, array $validMethods): bool
    {
        if(!isset(self::$validation)){
            self::$validation = new Validation();
        }
        $validation = self::$validation->setData($data);
        $validation->rule($key, $validMethods);
        return $validation->validation() !== FALSE;
    }

    private function getParameterBagBoot()
    {
        if(isset(self::$get)){
            return;
        }
        self::$get = new ParameterBag(!empty($_GET) ? $_GET : [], ['isMulti' => false]);
    }

    private function postParameterBagBoot()
    {
        if(isset(self::$post)){
            return;
        }
        self::$post = new ParameterBag(!empty($_POST) ? $_POST : [], ['isMulti' => false]);
    }

    private function rawParameterBagBoot()
    {
        if(isset(self::$raw)){
            return;
        }
        $body = @\file_get_contents('php://input');

        if ($body === FALSE) {
            $data = [];
        } else {
            $data = \json_decode($body, true);
        }

        self::$raw = new ParameterBag($data ? $data : [], ['isMulti' => false]);
    }

}
