<?php
/**
 * Stack.php
 *
 * This file is part of InitPHP.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0.2
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Input;

use \InitPHP\ParameterBag\ParameterBag;
use \InitPHP\Validation\Validation;

use function json_decode;
use function file_get_contents;
use function explode;
use function strpos;

final class Stack
{

    protected ParameterBag $get;
    protected ParameterBag $post;
    protected ParameterBag $raw;
    protected Validation $validation;

    public function __construct()
    {
        $this->resolve();
    }

    public function __destruct()
    {
        $this->validation->clear();
        $this->get->close();
        $this->post->close();
        $this->raw->close();
    }

    public function get(string $key, $default = null, ?array $validation = null)
    {
        if(!$this->get->has($key)){
            return $default;
        }
        $data = $this->get->get($key, $default);
        if(empty($validation)){
            return $data;
        }
        return $this->validData($this->get->all(), $key, $validation) ? $data : $default;
    }

    public function post(string $key, $default = null, ?array $validation = null)
    {
        if(!$this->post->has($key)){
            return $default;
        }
        $data = $this->post->get($key, $default);
        if(empty($validation)){
            return $data;
        }
        return $this->validData($this->post->all(), $key, $validation) ? $data : $default;
    }

    public function raw(string $key, $default = null, ?array $validation = null)
    {
        if(!$this->raw->has($key)){
            return $default;
        }
        $data = $this->raw->get($key, $default);
        if(empty($validation)){
            return $data;
        }
        return $this->validData($this->raw->all(), $key, $validation) ? $data : $default;
    }

    public function getPost(string $key, $default = null, ?array $validation = null)
    {
        return $this->get->has($key) ? $this->get($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function getRaw(string $key, $default = null, ?array $validation = null)
    {
        return $this->get->has($key) ? $this->get($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function getPostRaw(string $key, $default = null, ?array $validation = null)
    {
        if($this->get->has($key)){
            return $this->get($key, $default, $validation);
        }
        return $this->post->has($key) ? $this->post($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function getRawPost(string $key, $default = null, ?array $validation = null)
    {
        if($this->get->has($key)){
            return $this->get($key, $default, $validation);
        }
        return $this->raw->has($key) ? $this->raw($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function postGet(string $key, $default = null, ?array $validation = null)
    {
        return $this->post->has($key) ? $this->post($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function postRaw(string $key, $default = null, ?array $validation = null)
    {
        return $this->post->has($key) ? $this->post($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function postGetRaw(string $key, $default = null, ?array $validation = null)
    {
        if($this->post->has($key)){
            return $this->post($key, $default, $validation);
        }
        return $this->get->has($key) ? $this->get($key, $default, $validation) : $this->raw($key, $default, $validation);
    }

    public function postRawGet(string $key, $default = null, ?array $validation = null)
    {
        if($this->post->has($key)){
            return $this->post($key, $default, $validation);
        }
        return $this->raw->has($key) ? $this->raw($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function rawGet(string $key, $default = null, ?array $validation = null)
    {
        return $this->raw->has($key) ? $this->raw($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function rawPost(string $key, $default = null, ?array $validation = null)
    {
        return $this->raw->has($key) ? $this->raw($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function rawGetPost(string $key, $default = null, ?array $validation = null)
    {
        if($this->raw->has($key)){
            return $this->raw($key, $default, $validation);
        }
        return $this->get->has($key) ? $this->get($key, $default, $validation) : $this->post($key, $default, $validation);
    }

    public function rawPostGet(string $key, $default = null, ?array $validation = null)
    {
        if($this->raw->has($key)){
            return $this->raw($key, $default, $validation);
        }
        return $this->post->has($key) ? $this->post($key, $default, $validation) : $this->get($key, $default, $validation);
    }

    public function hasGet(string $key): bool
    {
        return $this->get->has($key);
    }

    public function hasPost(string $key): bool
    {
        return $this->post->has($key);
    }

    public function hasRaw(string $key): bool
    {
        return $this->raw->has($key);
    }

    protected function getValidation(): Validation
    {
        if(!isset($this->validation)){
            $this->validation = new Validation();
        }
        return $this->validation;
    }

    private function validData(array $data, string $key, array $validation): bool
    {
        $validate = $this->getValidation()->setData($data);
        $validate->rule($key, $validation);
        return $validate->validation() !== FALSE;
    }

    private function resolve(): void
    {
        $this->get = new ParameterBag(($_GET ?? []), [
            'isMulti'   => false
        ]);

        if(isset($_POST) && !empty($_POST)){
            $this->post = new ParameterBag($_POST, [
                'isMulti'   => false
            ]);
        }

        if(($inputs = @file_get_contents("php://input")) !== FALSE){
            $raws = json_decode($inputs, true);
            $this->raw = new ParameterBag((!empty($raws) ? $raws : []) , [
                'isMulti'   => false
            ]);

            if(
                ($raws === null || $raws === FALSE)
                && !isset($this->post)
                && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'
                && !empty($inputs)
                && (strpos($inputs, '&') !== FALSE || strpos($inputs, '=') !== FALSE)
            ){
                $posts = [];
                $parse = explode('&', $inputs);
                foreach ($parse as $argument) {
                    if(strpos($argument, '=') === FALSE){
                        continue;
                    }
                    $value = explode('=', $argument, 2);
                    $posts[$value[0]] = $value[1];
                }
                $this->post = new ParameterBag($posts, [
                    'isMulti'   => false,
                ]);
            }
        }

        if(!isset($this->post)){
            $this->post = new ParameterBag([], [
                'isMulti'   => false
            ]);
        }
    }

}
