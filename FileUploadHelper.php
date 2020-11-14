<?php
/**
 * PHP helper class for uploading a file via HTTP POST request.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2018 Maksim T.
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/zapalm/fileUploadHelper GitHub
 * @link      http://zapalm.ru/ Author's Homepage
 */

namespace zapalm\fileUploadHelper;

/**
 * Загрузчик файлов.
 *
 * @author Maksim T. <zapalm@yandex.com>
 */
class FileUploadHelper
{
    /** @var string Точка входа */
    protected $bootstrapUrl;

    /** @var resource Ресурс cURL */
    protected $curlHandler;

    /** @var array Опции cURL */
    protected $curlOptions = array(
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE        => false,
    );

    /** @var string Ответ на запрос */
    protected $response;

    /**
     * Конструктор.
     *
     * @param bool $verbose Указать true, чтобы вывести детали выполнения запроса.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function __construct($verbose = false)
    {
        $this->curlOptions[CURLOPT_VERBOSE] = $verbose;
    }

    /**
     * Установить точку входа.
     *
     * @param string $url
     *
     * @return self
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function setBootstrapUrl($url)
    {
        $this->bootstrapUrl = $url;

        return $this;
    }

    /**
     * Отправить post-запрос.
     *
     * @param string $uri    Дополнительные параметры для URL.
     * @param array  $params Параметры POST-запроса в формате [name => value].
     * @param array  $files  Список файлов в формате [name => path].
     *
     * @return self
     *
     * @throws \Exception
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function sendPostContent($uri, array $params = array(), array $files = array())
    {
        $this->curlHandler = curl_init($this->bootstrapUrl . $uri);
        $this->preparePostData($params, $files);
        $this->response = curl_exec($this->curlHandler);
        $this->response = str_replace("\xEF\xBB\xBF", '', $this->response); // Removing UTF BOM (byte-order mark)

        if ($this->curlOptions[CURLOPT_VERBOSE]) {
            print_r(curl_getinfo($this->curlHandler));
        }

        curl_close($this->curlHandler);

        return $this;
    }

    /**
     * Отправить POST-запрос с сырыми данными.
     *
     * @param string $uri  Дополнительные параметры для URL.
     * @param string $file Путь к файлу.
     *
     * @return self
     *
     * @throws \Exception
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function sendRowContent($uri, $file)
    {
        $this->curlHandler = curl_init($this->bootstrapUrl . $uri);
        $this->prepareRowData($file);

        $this->response = curl_exec($this->curlHandler);
        if (is_string($this->response)) {
            $this->response = str_replace("\xEF\xBB\xBF", '', $this->response); // Removing UTF BOM (byte-order mark)
        }

        if ($this->curlOptions[CURLOPT_VERBOSE]) {
            print_r(curl_getinfo($this->curlHandler));
        }

        curl_close($this->curlHandler);

        return $this;
    }

    /**
     * Получить ответ на запрос.
     *
     * @return string|bool
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Установить куку.
     *
     * @param string $name  Наименование куки.
     * @param string $value Значение куки.
     *
     * @return self
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function setCookie($name, $value)
    {
        $this->curlOptions[CURLOPT_COOKIE] = $name . '=' . urlencode($value);

        return $this;
    }

    /**
     * Установить логин и пароль для базовой авторизации.
     *
     * @param string $login    Логин.
     * @param string $password Пароль.
     *
     * @return self
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function setAuth($login, $password)
    {
        $this->curlOptions[CURLOPT_USERPWD] = $login . ':' . $password;

        return $this;
    }

    /**
     * Подготовить сырые данные для отправки.
     *
     * @param string $file Путь к файлу.
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    protected function prepareRowData($file)
    {
        $this->curlOptions[CURLOPT_POSTFIELDS] = file_get_contents($file);

        $result = curl_setopt_array($this->curlHandler, $this->curlOptions);
        if (!$result) {
            throw new \Exception('Ошибка при установки опций cURL для запроса.');
        }

        return $result;
    }

    /**
     * Подготовить данные для отправки.
     *
     * @param array  $params Параметры POST-запроса в формате [name => value].
     * @param array  $files  Список файлов в формате [name => path].
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    protected function preparePostData(array $params = array(), array $files = array())
    {
        // Invalid characters for "name" and "filename"
        $disallow = array("\0", "\"", "\r", "\n");

        // Build normal parameters
        $body = array();
        foreach ($params as $name => $value) {
            $name   = str_replace($disallow, '_', $name);
            $body[] = implode("\r\n", array(
                'Content-Disposition: form-data; name="' . $name . '"',
                '',
                filter_var($value),
            ));
        }

        // Build file parameters
        foreach ($files as $name => $path) {
            switch (true) {
                case (false === ($path = realpath(filter_var($path)))):
                case !is_file($path):
                case !is_readable($path):
                    throw new \Exception('Массив файлов задан неверно.');
            }
            $data   = file_get_contents($path);
            $path   = call_user_func('end', explode(DIRECTORY_SEPARATOR, $path));
            $name   = str_replace($disallow, '_', $name);
            $path   = str_replace($disallow, '_', $path);
            $body[] = implode("\r\n",
                array(
                    'Content-Disposition: form-data; name="' . $name . '"; filename="' . $path . '"',
                    'Content-Type: application/octet-stream',
                    '',
                    $data,
                ));
        }

        // Generate safe boundary
        do {
            $boundary = '---------------------' . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

        // Add boundary for each parameters
        array_walk($body,
            function (&$part) use ($boundary) {
                $part = "--{$boundary}\r\n{$part}";
            });

        // Add final boundary
        $body[] = '--' . $boundary . '--';
        $body[] = '';

        $this->curlOptions[CURLOPT_POSTFIELDS] = implode("\r\n", $body);
        $this->curlOptions[CURLOPT_HTTPHEADER] = array(
            'Expect: 100-continue',
            'Content-Type: multipart/form-data; boundary=' . $boundary,
        );

        // Set options
        $result = curl_setopt_array($this->curlHandler, $this->curlOptions);
        if (!$result) {
            throw new \Exception('Ошибка при установки опций cURL для запроса.');
        }

        return $result;
    }
}