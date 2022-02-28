<?php

namespace Lijinhua\Email;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Lijinhua\Email\Messages\CodeMail;
use Lijinhua\Email\Storage\StorageInterface;

class Email
{

    /**
     * 存储器
     *
     * @var StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * 缓存key
     *
     * @var string
     */
    protected string $key;

    /**
     * @param  StorageInterface  $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * 发送验证码
     *
     * @param  string  $emailAddress
     * @param  string|null  $codeMailClass
     */
    public function sendCode(string $emailAddress, ?string $codeMailClass = null)
    {
        $this->setKey($emailAddress);

        $code = $this->getCodeFromStorage();
        if ($this->needNewCode($code)) {
            $code = $this->getNewCode($emailAddress);
        }

        $validMinutes = (int) config('email.code.validMinutes', 5);

        if (is_null($codeMailClass)) {
            $mailable = new CodeMail($code->code, $validMinutes);
        } else {
            $mailable = new $codeMailClass($code->code, $validMinutes);
        }

        if (config('email.debug')) {
            Mail::mailer('log')->to([$emailAddress])->send($mailable);
        } else {
            Mail::to([$emailAddress])->send($mailable);
        }
    }

    /**
     * 设置缓存
     *
     * @param  string  $key
     */
    public function setKey(string $key)
    {
        $key       = 'email.' . $key;
        $this->key = md5($key);
    }

    /**
     * 从存储器中获取验证码
     *
     * @return mixed
     */
    public function getCodeFromStorage(): mixed
    {
        return $this->storage->get($this->key, '');
    }

    /**
     * 获取新验证码
     *
     * @param  string  $emailAddress
     * @return Code
     */
    public function getNewCode(string $emailAddress): Code
    {
        $code = $this->generateCode($emailAddress);

        $this->storage->set($this->key, $code);

        return $code;
    }

    /**
     * 生成验证码
     *
     * @param  string  $emailAddress
     * @return Code
     */
    public function generateCode(string $emailAddress): Code
    {
        if (config('email.debug')) {
            $code = config('email.code_test');
        } else {
            $length     = (int) config('email.code.length', 5);
            $characters = '0123456789';
            $charLength = strlen($characters);
            $code       = '';
            for ($i = 0; $i < $length; ++$i) {
                $code .= $characters[mt_rand(0, $charLength - 1)];
            }
        }

        $validMinutes = (int) config('email.code.validMinutes', 5);

        return new Code($emailAddress, $code, false, 0, Carbon::now()->addMinutes($validMinutes));
    }

    /**
     * 检查验证码
     *
     * @param  string  $emailAddress
     * @param  string  $inputCode
     * @return bool
     */
    public function checkCode(string $emailAddress, string $inputCode = ''): bool
    {
        $this->setKey($emailAddress);

        $code = $this->storage->get($this->key, '');

        if (empty($code)) {
            return false;
        }

        if ($code->code == $inputCode) {
            return true;
        }

        $code->put('attempts', $code->attempts + 1);

        $this->storage->set($this->key, $code);

        return false;
    }

    /**
     * 清除验证
     *
     * @param  string  $emailAddress
     */
    public function clearCode(string $emailAddress)
    {
        $this->setKey($emailAddress);

        $this->storage->forget($this->key);
    }

    /**
     * 获取存储器
     *
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * 设置存储器
     *
     * @param  StorageInterface  $storage
     */
    public function setStorage(StorageInterface $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * 是否可以发送
     *
     * @param  string  $emailAddress
     * @return bool
     */
    public function canSend(string $emailAddress): bool
    {
        $this->setKey($emailAddress);

        $code = $this->storage->get($this->key, '');

        if (empty($code) || $code->sentAt < Carbon::now()->addMinutes(-1)) {
            return true;
        }

        return false;
    }

    /**
     * 是否需要生成新的验证码
     *
     * @param $code
     * @return bool
     */
    protected function needNewCode($code): bool
    {
        if (empty($code)) {
            return true;
        }

        return $this->checkAttempts($code);
    }

    /**
     * 检查尝试次数
     *
     * @param $code
     * @return bool
     */
    private function checkAttempts($code): bool
    {
        $maxAttempts = config('email.code.maxAttempts');

        if ($code->expireAt > Carbon::now() && $code->attempts < $maxAttempts) {
            return false;
        }

        return true;
    }

}