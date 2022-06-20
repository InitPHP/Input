<?php
/**
 * Stack.php
 *
 * This file is part of InitPHP.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0.1
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Input;

use \InitPHP\ParameterBag\ParameterBag;
use \InitPHP\Validation\Validation;

use function is_array;
use function array_keys;
use function json_decode;
use function file_get_contents;
use function explode;
use function strpos;

/**
 * @property-read null|ParameterBag $get
 * @property-read null|ParameterBag $post
 * @property-read null|ParameterBag $raw
 * @property-read null|ParameterBag $files
 */
final class Stack
{

    protected static ParameterBag $PBGet;
    protected static ParameterBag $PBPost;
    protected static ParameterBag $PBRaw;
    protected static ParameterBag $PBFiles;
    protected static Validation $validation;

    public function __construct()
    {
        $this->getParameterBagStart();
        $this->postParameterBagStart();
        $this->rawParameterBagStart();
        $this->filesParameterBagStart();
    }

    public function __destruct()
    {
        if(isset(self::$validation)){
            self::$validation->clear();
        }
        if(isset(self::$PBFiles)){
            self::$PBFiles->close();
        }
        if(isset(self::$PBGet)){
            self::$PBGet->close();
        }
        if(isset(self::$PBPost)){
            self::$PBPost->close();
        }
        if(isset(self::$PBRaw)){
            self::$PBRaw->close();
        }
    }

    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'get':
                return self::$PBGet ?? null;
            case 'post':
                return self::$PBPost ?? null;
            case 'files':
                return self::$PBFiles ?? null;
            case 'raw':
                return self::$PBRaw ?? null;
            default:
                return null;
        }
    }

    public function get(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBGet->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function post(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBPost->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function raw(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBRaw->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function files(string $key, $default = null)
    {
        $this->filesParameterBagStart();
        return self::$PBFiles->get($key, $default);
    }

    public function getPost(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBGet->has($key) ? self::$PBGet->get($key) : self::$PBPost->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function getRaw(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBGet->has($key) ? self::$PBGet->get($key) : self::$PBRaw->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function getPostRaw(string $key, $default = null, ?array $validation = null)
    {
        if(self::$PBGet->has($key)){
            $data = self::$PBGet->get($key);
        }else{
            $data = self::$PBPost->has($key) ? self::$PBPost->get($key) : self::$PBRaw->get($key, $default);
        }
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function getRawPost(string $key, $default = null, ?array $validation = null)
    {
        if(self::$PBGet->has($key)){
            $data = self::$PBGet->get($key);
        }else{
            $data = self::$PBRaw->has($key) ? self::$PBRaw->get($key) : self::$PBPost->get($key, $default);
        }
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function postGet(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBPost->has($key) ? self::$PBPost->get($key) : self::$PBGet->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function postRaw(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBPost->has($key) ? self::$PBPost->get($key) : self::$PBRaw->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function postGetRaw(string $key, $default = null, ?array $validation = null)
    {
        if(self::$PBPost->has($key)){
            $data = self::$PBPost->get($key);
        }else{
            $data = self::$PBGet->has($key) ? self::$PBGet->get($key) : self::$PBRaw->get($key, $default);
        }
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function postRawGet(string $key, $default = null, ?array $validation = null)
    {
        if(self::$PBPost->has($key)){
            $data = self::$PBPost->get($key);
        }else{
            $data = self::$PBRaw->has($key) ? self::$PBRaw->get($key) : self::$PBGet->get($key, $default);
        }
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function rawGet(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBRaw->has($key) ? self::$PBRaw->get($key) : self::$PBGet->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function rawPost(string $key, $default = null, ?array $validation = null)
    {
        $data = self::$PBRaw->has($key) ? self::$PBRaw->get($key) : self::$PBPost->get($key, $default);
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function rawGetPost(string $key, $default = null, ?array $validation = null)
    {
        if(self::$PBRaw->has($key)){
            $data = self::$PBRaw->get($key);
        }else{
            $data = self::$PBGet->has($key) ? self::$PBGet->get($key) : self::$PBPost->get($key, $default);
        }
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function rawPostGet(string $key, $default = null, ?array $validation = null)
    {
        if(self::$PBRaw->has($key)){
            $data = self::$PBRaw->get($key);
        }else{
            $data = self::$PBPost->has($key) ? self::$PBPost->get($key) : self::$PBGet->get($key, $default);
        }
        return empty($validation) ? $data : $this->validData($data, $validation, $default);
    }

    public function hasGet(string $key): bool
    {
        return self::$PBGet->has($key);
    }

    public function hasPost(string $key): bool
    {
        return self::$PBPost->has($key);
    }

    public function hasRaw(string $key): bool
    {
        return self::$PBRaw->has($key);
    }

    public function hasFiles(string $key): bool
    {
        $this->filesParameterBagStart();
        return self::$PBFiles->has($key);
    }

    private function getValidation(): Validation
    {
        if(!isset(self::$validation)){
            self::$validation = new Validation();
        }
        return self::$validation;
    }

    private function validData($data, array $validation, $default)
    {
        $validate = $this->getValidation()->setData(['data' => $data]);
        $validate->rule('data', $validation);
        return $validate->validation() ? $data : $default;
    }

    private function getParameterBagStart(): void
    {
        if(isset(self::$PBGet)){
            return;
        }
        self::$PBGet = new ParameterBag(($_GET ?? []), [
            'isMulti'   => false
        ]);
    }

    private function postParameterBagStart(): void
    {
        if(isset(self::$PBPost)){
            return;
        }
        $bagOptions = ['isMulti' => false];
        if(isset($_POST) && !empty($_POST)){
            self::$PBPost = new ParameterBag($_POST, $bagOptions);
            return;
        }
        if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $postInputs = @file_get_contents("php://input");
            if(empty($postInputs) || strpos($postInputs, '&') === FALSE){
                self::$PBPost = new ParameterBag([], $bagOptions);
                return;
            }
            $data = [];
            $parse = explode('&', $postInputs);
            foreach ($parse as $input) {
                if(strpos($input, '=') === FALSE){
                    continue;
                }
                $in = explode('=', $input, 2);
                $data[$in[0]] = $in[1];
            }
            self::$PBRaw = new ParameterBag($data, $bagOptions);
            return;
        }
        self::$PBPost = new ParameterBag([], $bagOptions);
    }

    private function filesParameterBagStart(): void
    {
        if(isset(self::$PBFiles)){
            return;
        }
        $data = [];
        if(isset($_FILES) && !empty($_FILES)){
            foreach ($_FILES as $key => $value) {
                if(!is_array($value['name'])){
                    $data[$key] = $value;
                    continue;
                }
                $data[$key] = $this->normalizeFiles($value);
            }
        }
        self::$PBFiles = new ParameterBag($data, [
            'isMulti'   => false,
        ]);
    }

    private function rawParameterBagStart(): void
    {
        if(isset(self::$PBRaw)){
            return;
        }
        $rawInputs = @file_get_contents("php://input");
        $data = empty($rawInputs) ? [] : (array)json_decode($rawInputs, true);
        self::$PBRaw = new ParameterBag($data, [
            'isMulti'   => false,
        ]);
    }

    private function normalizeFiles( $files ): array
    {
        $res = [];
        $mainKeys = array_keys($files);
        $fileKeys = array_keys($files['name']);
        foreach ($fileKeys as $fileId) {
            foreach ($mainKeys as $key) {
                $res[$fileId][$key] = $files[$key][$fileId];
            }
        }
        return $res;
    }

}
