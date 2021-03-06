<?php

namespace Bluefin;

use Symfony\Component\Yaml\Yaml;
use Bluefin\Log;

class App implements ContextProviderInterface
{
    /**
     * @var App
     */
    private static $__instance;

    /**
     * @static
     * @return App
     */
    public static function getInstance()
    {
        if (!isset(self::$__instance))
        {
            self::$__instance = new self();

            self::$__instance->_bootstrap();
        }

        return self::$__instance;
    }

    /**
     * 断言
     * @static
     * @throws Exception\AssertException
     * @param  $expression 布尔表达式
     * @param string $extraMessage 额外错误信息
     * @return void
     */
    public static function assert($expression, $extraMessage = null)
    {
        if (ASSERT_BEHAVIOR != 'disable' && !$expression)
        {
            $traces = debug_backtrace();
            $cause = $traces[0];
            $message = "Assertion failed at line({$cause['line']}) in file \"{$cause['file']}\"!";
            if (isset($extraMessage))
            {
                $message .= " {$extraMessage}";
            }

            if (ASSERT_BEHAVIOR == 'throw')
            {
                throw new \Bluefin\Exception\AssertException($message);
            }

            if (ASSERT_BEHAVIOR == 'error')
            {
                trigger_error($message, E_USER_ERROR);
            }

            //ASSERT_BEHAVIOR == 'ignore'
            App::getInstance()->log()->error($message);
        }
    }

    /**
     * @static
     * @param $ymlFile
     * @return array
     */
    public static function loadYmlFileEx($ymlFile)
    {
        $yml = Yaml::load($ymlFile);
        if (is_array($yml)) self::expandYml($yml);
        return $yml;
    }

    public static function expandYml(array &$yml)
    {
        foreach ($yml as $key => &$config)
        {
            if (Convention::CONFIG_KEYWORD_INCLUDE === $key)
            {
                unset($yml[Convention::CONFIG_KEYWORD_INCLUDE]);

                if (is_array($config))
                {                   
                    foreach ($config as $includeFile)
                    {
                        if (!file_exists($includeFile))
                        {
                            error_log("File \"{$includeFile}\" included in the configuration does not exist.");
                            continue;
                        }

                        $loaded = self::loadYmlFileEx($includeFile);
                        $yml = array_merge($yml, is_array($loaded) ? $loaded : array($loaded));
                    }
                }
                else
                {
                    if (!file_exists($config))
                    {
                        error_log("File \"{$config}\" included in the configuration does not exist.");
                        continue;
                    }

                    $yml = array_merge($yml, self::loadYmlFileEx($config));
                }
            }
            else if (is_array($config))
            {
                self::expandYml($config);
            }
        }
    }

    public static function createPersistenceObject(array $config)
    {
        $type = array_try_get($config, 'type');
        if (!isset($type))
        {
            throw new \Bluefin\Exception\ConfigException("Missing persistence type!");
        }

        $options = array_try_get($config, 'options');

        $type = usw_to_pascal($type);
        $class = "\\Bluefin\\Persistence\\{$type}";

        return new $class($options);
    }

    private $_startTime;

    /**
     * @var \AppExtender
     */
    private $_appExtender;

    private $_config;
    private $_basePath;
    private $_rootUrl;

    private $_log = [];
    private $_db = [];
    private $_auth = [];
    private $_cache = [];
    private $_session = [];

    private $_request;
    private $_response;

    private $_currentLocale;
    private $_localeText;

    private $_registry = [];

    private $_appSession;

    /**
     * @var \Bluefin\Gateway
     */
    private $_gateway;

    private function __construct()
    {
        if (!defined('BLUEFIN_VERSION'))
        {
            require_once realpath(__DIR__) . '/bluefin.php';
        }
    }

    public function __destruct()
    {
    }

    public function __clone()
    {
        App::assert(false);
    }

    public function __wakeup()
    {
        App::assert(false);
    }

    public function getContext($name)
    {
        switch ($name)
        {
            case 'timestamp':
                return time();

            case 'locale':
                return $this->currentLocale();

            case 'base':
                return $this->basePath();

            case 'root':
                return $this->rootUrl();

            case 'elapsed':
                return (int)($this->elapsedTime() * 1000000) / 1000.0;

            case 'session':
                return $this->session()->get();

            case 'session_id':
                return session_id();

            default:
                throw new \Bluefin\Exception\ServerErrorException("Unknown app parameter: {$name}!");
        }
    }

    public function startGateway()
    {
        $this->_gateway->service();
    }

    /**
     * @return int
     */
    public function startTime()
    {
        return $this->_startTime;
    }

    public function elapsedTime()
    {
        $currentTime = microtime(true);

        return $currentTime - $this->_startTime;
    }

    public function config($section = null)
    {
        return isset($section) ? array_try_get($this->_config, $section) : $this->_config;
    }

    public function basePath()
    {
        return $this->_basePath;
    }

    public function rootUrl()
    {
        return $this->_rootUrl;
    }

    public function gateway()
    {
        return $this->_gateway;
    }

    /**
     * Gets a logger.
     *
     * @param string $id
     * @return \Bluefin\Log
     * @throws Exception\ConfigException
     */
    public function log($id = Convention::CONFIG_KEYWORD_DEFAULT)
    {        
        //如果日志对象还不存在
        if (!isset($this->_log[$id]))
        {
            if (array_key_exists(Convention::CONFIG_SECTION_LOG, $this->_config))
            {
                $logSection = $this->_config[Convention::CONFIG_SECTION_LOG];

                if (!array_key_exists($id, $logSection))
                {
                    throw new \Bluefin\Exception\ConfigException("'{$id}' log config not found!");
                }

                //根据标识读取配置
                $loggersConfig = $logSection[$id];
                is_array($loggersConfig) || ($loggersConfig = array($loggersConfig));

                //创建日志对象
                $log = new Log();

                foreach ($loggersConfig as $loggerConfig)
                {
                    $loggerType = array_try_get($loggerConfig, 'type', 'file', true);

                    $loggerClass = "\\Bluefin\\Logger\\" . usw_to_pascal($loggerType) . "Logger";

                    /**
                     * @var \Bluefin\Logger\LoggerInterface
                     */
                    $logger = new $loggerClass($loggerConfig);

                    $log->addLogger($logger);
                }
            }
            else
            {
                $log = Dummy::getInstance();
            }

            $this->_log[$id] = $log;
        }

        return $this->_log[$id];
    }

    /**
     * @param $id
     * @return \Bluefin\Persistence\PersistenceInterface
     * @throws Exception\ConfigException
     */
    public function cache($id)
    {
        //如果日志对象还不存在
        if (!isset($this->_cache[$id]))
        {
            if (!array_key_exists(Convention::CONFIG_SECTION_CACHE, $this->_config))
            {
                throw new \Bluefin\Exception\ConfigException("Cache config not found!");
            }

            $cacheSection = $this->_config[Convention::CONFIG_SECTION_CACHE];

            if (!array_key_exists($id, $cacheSection))
            {
                throw new \Bluefin\Exception\ConfigException("'{$id}' cache config not found!");
            }

            //根据标识读取配置
            $this->_cache[$id] = self::createPersistenceObject($cacheSection[$id]);
        }

        return $this->_cache[$id];
    }

    public function addTranslation($domain, $message, $translation)
    {
        if (!isset($this->_localeText)) return;

        if (!array_key_exists($domain, $this->_localeText))
        {
            $this->_loadLocale($domain);
        }

        $domainText = &$this->_localeText[$domain];
        $domainText[$message] = is_null($translation) ? $message : $translation;

        if (ENABLE_LOCALE_EXPORT)
        {
            $this->_exportLocale($domain);
        }
    }

    public function translate($message, $domain)
    {
        if (!isset($this->_localeText)) return $message;

        if (!array_key_exists($domain, $this->_localeText))
        {
            $this->_loadLocale($domain);
        }

        $domainText = &$this->_localeText[$domain];
        App::Assert(is_array($domainText), "Unknown locale domain: {$domain}, or maybe in wrong encoding.");

        if (array_key_exists($message, $domainText) && $domainText[$message] != '')
        {
            return $domainText[$message];
        }
        else
        {
            $domainText[$message] = '';

           /* if (ENABLE_LOCALE_EXPORT)
            {
                $this->_exportLocale($domain);
            }
           */
        }

        return $message;
    }

    /**
     * @param $id
     * @return \Bluefin\Data\Database
     * @throws Exception\ConfigException
     */
    public function db($id)
    {
        //如果数据库对象还不存在
        if (!isset($this->_db[$id]))
        {
            if (!array_key_exists(Convention::CONFIG_SECTION_DB, $this->_config))
            {
                throw new \Bluefin\Exception\ConfigException("Db config not found!");
            }

            $dbSection = $this->_config[Convention::CONFIG_SECTION_DB];

            if (!array_key_exists($id, $dbSection))
            {
                throw new \Bluefin\Exception\ConfigException("'{$id}' db config not found!");
            }

            //根据标识读取配置
            $dbConfig = $dbSection[$id];

            if (!array_key_exists('class', $dbConfig))
            {
                throw new \Bluefin\Exception\ConfigException("Db class for '{$id}' not found!");
            }

            $dbClass = $dbConfig['class'];

            $this->_db[$id] = new $dbClass(array_try_get($dbConfig, 'config'));
        }

        return $this->_db[$id];
    }

    public function currentLocale()
    {
        return $this->_currentLocale;
    }

    /**
     * @param null $namespace
     * @return Persistence\Session
     */
    public function session($namespace = null)
    {
        isset($namespace) || ($namespace = $this->_appSession);

        if (!isset($this->_session[$namespace]))
        {
            $this->_session[$namespace] = new \Bluefin\Persistence\Session(['namespace' => $namespace]);
        }

        return $this->_session[$namespace];
    }

    /**
     * @param $authName
     * @return \Bluefin\Auth\AuthInterface
     * @throws Exception\ConfigException
     */
    public function auth($authName)
    {
        if (!array_key_exists(Convention::CONFIG_SECTION_AUTH, $this->_config))
        {
            throw new \Bluefin\Exception\ConfigException("Auth config not found!");
        }

        //如果数据库对象还不存在
        if (!isset($this->_auth[$authName]))
        {
            $authSection = $this->_config[Convention::CONFIG_SECTION_AUTH];

            if (!array_key_exists($authName, $authSection))
            {
                throw new \Bluefin\Exception\ConfigException("'{$authName}' auth config not found!");
            }

            //根据标识读取配置
            $authConfig = $authSection[$authName];

            if (!array_key_exists('class', $authConfig))
            {
                throw new \Bluefin\Exception\ConfigException("Auth class for '{$authName}' not found!");
            }

            $authClass = $authConfig['class'];

            $this->_auth[$authName] = new $authClass($authName, array_try_get($authConfig, 'config'));
        }

        return $this->_auth[$authName];
    }

    public function role($authName)
    {
        $namespace = make_dot_name('role', $authName);

        return $this->session($namespace);
    }

    /**
     * @return \Bluefin\Request
     */
    public function request()
    {
        //未创建Request对象，则创建Request对象
        if (!isset($this->_request))
        {
            $this->_request = Request::createFromGlobals();
        }

        return $this->_request;
    }

    /**
     * @return \Bluefin\Response
     */
    public function response()
    {
        //未创建Response对象，则创建Response对象
        if (!isset($this->_response))
        {
            $this->_response = new Response();
        }

        return $this->_response;
    }

    public function inRegistry($name)
    {
        return array_key_exists($name, $this->_registry);
    }

    public function getRegistry($name, $default = null)
    {
        return array_try_get($this->_registry, $name, $default);
    }

    public function setRegistry($name, $data)
    {
        $this->_registry[$name] = $data;
    }

    /**
     * Bootstrap the application object.
     */
    private function _bootstrap()
    {
        // 设置启动时间用于调试
        $this->_startTime = microtime(true);

        if (file_exists(APP . '/AppExtender.php'))
        {
            require_once APP . '/AppExtender.php';

            $this->_appExtender = new \AppExtender();
        }
        else
        {
            $this->_appExtender = Dummy::getInstance();
        }

        $this->_appExtender->registerVarTextModifiers();

        // 读取配置文件
        $this->_loadConfig();

        // 应用配置
        $this->_applyAppSettings();

        if (!defined('STDOUT'))
        {
            // 如果不是CLI模式，启动Session
            $this->_startSession();
        }

        // 初始化多语言配置
        $this->_initializeLocale();
    }

    private function _loadConfig()
    {
        // 建立缓存目录
        ensure_dir_exist(CACHE, 0750);

        $globalConfigFile = CACHE . '/global.php';

        if (ENABLE_CACHE && file_exists($globalConfigFile))
        {
            // 启用缓存，而且配置文件的缓存存在
            $this->_config = require $globalConfigFile;
        }
        else
        {
            $rawConfigFile = APP_ETC . '/global.yml';
            if (file_exists($rawConfigFile))
            {
                $this->_config = self::loadYmlFileEx($rawConfigFile);
            }
            else
            {
                $this->_config = array();
            }

            if (ENABLE_CACHE)
            {
                // 如果启用缓存，则生成缓存
                save_var_to_php($globalConfigFile, $this->_config);
            }
        }
    }

    private function _applyAppSettings()
    {
        if (!array_key_exists(Convention::CONFIG_SECTION_APP, $this->_config)) return;

        $appSection = $this->_config[Convention::CONFIG_SECTION_APP];

        if (array_key_exists('timezone', $appSection))
        {
            date_default_timezone_set($appSection['timezone']);
        }

        if (array_key_exists('phpInternalEncoding', $appSection))
        {
            if (extension_loaded('mbstring'))
            {
                mb_internal_encoding($appSection['phpInternalEncoding']);
            }
            else
            {
                throw new \Bluefin\Exception\ServerErrorException("PHP extension 'mb_string' has not been loaded.");
            }
        }

        if (array_key_exists('requestOrder', $appSection))
        {
            $this->request()->setRequestOrder($appSection['requestOrder']);
        }

        $this->_basePath = str_pad_if(array_try_get($appSection, 'basePath', '/'), '/', true);
        $this->_rootUrl = array_try_get($appSection, 'rootUrl', $this->request()->getScheme() . '://' . $this->request()->getHttpHost()) . $this->_basePath;
        $this->_gateway = new Gateway();

        $this->_appSession = array_try_get($appSection, 'sessionNamespace', Convention::DEFAULT_SESSION_NAMESPACE);

        if (array_key_exists('sessionDomain', $appSection))
        {
            ini_set('session.cookie_domain', $appSection['sessionDomain']);
        }

        $this->log()->info("--------------------------------------------------------------------------------");
        //$this->log()->debug("REQ: " . $this->request()->getRequestUri());
    }

    private function _startSession()
    {
        if (!array_key_exists(Convention::CONFIG_SECTION_SESSION, $this->_config)) return;

        $sessionSection = $this->_config[Convention::CONFIG_SECTION_SESSION];

        //判断配置文件是否提供SessionSaveHandler类名
        if (isset($sessionSection['saveHandler']))
        {
            $saveHandler = $sessionSection['saveHandler'];

            if (array_key_exists('lifetime', $sessionSection))
            {
                ini_set('session.gc_maxlifetime', $sessionSection['lifetime']);
            }

            if ($saveHandler == 'custom')
            {
                if (!array_key_exists('class', $sessionSection))
                {
                    throw new \Bluefin\Exception\ConfigException("'class' is required for custom session save-handler!");
                }

                $saveHandler = new $sessionSection['class'](array_try_get($sessionSection, 'options', []));

                session_set_save_handler(
                    array($saveHandler, 'open'),
                    array($saveHandler, 'close'),
                    array($saveHandler, 'read'),
                    array($saveHandler, 'write'),
                    array($saveHandler, 'destroy'),
                    array($saveHandler, 'gc')
                );
            }
            else
            {
                ini_set('session.save_handler', $saveHandler);
                if (array_key_exists('savePath', $sessionSection))
                {
                    ini_set('session.save_path', $sessionSection['savePath']);
                }
            }
        }

        //Hack for Flash Post
		if ($_SERVER['HTTP_USER_AGENT'] == 'Shockwave Flash' && isset($_POST['PHPSESSID']))
        {
            session_id($_POST['PHPSESSID']);            
            unset($_POST['PHPSESSID']);            
        }

        try
        {
            session_start();
        }
        catch (\Exception $e)
        {
            die($e->getMessage());
        }

        $appSession = $this->session();
        $request = $this->request();

        $logPost =  $this->_unsetPassword($request->getPostParams()) ;
        $logQuery = $request->getQueryParams();

        if(!empty($logQuery))
        {
            log_debug('__QUERY__:', $logQuery);
        }

        if(!empty($logPost))
        {
            log_debug('__POST__:', $logPost);
        }

        if ($appSession->isEmpty())
        {
            $appSession->merge(
                array(
                    'counter' => 0,
                    'created_at' => $request->getServerParam('REQUEST_TIME')
                )
            );
        }
        else
        {
            //session_regenerate_id(true);

            $counter = (int)$appSession->get('counter');

            $appSession->merge(
                array(
                    'counter' => ++$counter,
                    'updated_at' => $request->getServerParam('REQUEST_TIME')
                )
            );
        }
    }

    private function _unsetPassword($input)
    {
        if(!is_array($input))
        {
            return $input;
        }

        foreach ($input as $key => $value)
        {
            if(strpos($key, 'password') !== false)
            {
                unset($input[$key]);
            }
        }
        return $input;
    }

    private function _initializeLocale()
    {
        //TODO: 修改为按需加载

        if (!array_key_exists(Convention::CONFIG_SECTION_LOCALE, $this->_config)) return;

        $localeSection = $this->_config[Convention::CONFIG_SECTION_LOCALE];

        $localeParameter = array_try_get($localeSection, 'requestName', Convention::DEFAULT_LOCALE_REQUEST_NAME);
        $supportedLocales = array_try_get($localeSection, 'supportedLocales', array(Convention::DEFAULT_LOCALE_VALUE));
        $useSession = array_try_get($localeSection, 'useSession', Convention::DEFAULT_LOCALE_USE_SESSION);
        $useCache = array_try_get($localeSection, 'useCache', Convention::DEFAULT_LOCALE_USE_CACHE);
        $defaultLocale = array_try_get($localeSection, 'defaultLocale', Convention::DEFAULT_LOCALE_VALUE);

        // try get lcid from request
        $lcid = $this->request()->get($localeParameter);
        
        if (empty($lcid) && $useSession && isset($_SESSION[Convention::SESSION_CURRENT_LOCALE]))
        {
            $lcid = $_SESSION[Convention::SESSION_CURRENT_LOCALE];
        }

        if (!empty($lcid))
        {
            // check if it is supported
            if (!in_array($lcid, $supportedLocales,true))
            {
                $this->log()->info("Request Error! Requested locale[{$lcid}] not supported!");
                $lcid = null;
            }
        }

        if (empty($lcid))
        {
            // try get lcid from header
            $languages = $this->request()->getAcceptLanguages();
            if (!empty($languages) && !in_array($defaultLocale,$languages,true))
            {
                $supportedLanguages = array_intersect($languages, $supportedLocales);

                if (!empty($supportedLanguages))
                {
                    $lcid = array_shift($supportedLanguages);
                }
                else
                {
                    $lcid = $defaultLocale;
                    $this->log()->info('Request Error! HTTP_ACCEPT_LANGUAGE[' . implode(' ', $languages) . '] not supported!');
                }
            }
            else
            {
                $lcid = $defaultLocale;
            }
        }

        setlocale(LC_ALL, $lcid);
        $this->_currentLocale = $lcid;

        if ($useSession)
        {
            $_SESSION[Convention::SESSION_CURRENT_LOCALE] = $lcid;
        }

        $this->_localeText = array();

        /*
        if ($useCache)
        {

            $cache = $this->cache('locale');
            if (!isset($cache))
            {
                throw new \Bluefin\Exception\ConfigException("Locale cache not found in config while 'useCache' is specified.");
            }

            $key = Convention::CACHE_KEY_PREFIX_LOCALE . $this->_currentLocale;
            $localeCache = $cache->get($key);
            if (isset($localeCache))
            {
                $this->_localeText = unserialize($localeCache);
                //[+]DEBUG
                $this->log()->debug('Cached locale text loaded.');
                //[-]DEBUG
            }
        }
        */

        /*
        if (!isset($this->_localeText))
        {


            if (ENABLE_LOCALE_EXPORT)
            {
                $this->_localePath = array();
            }

            $fullPath = BLUEFIN_BUILTIN . '/locale/' . $this->_currentLocale . '/*' . Convention::FILE_TYPE_LOCALE_FILE;

            foreach (glob($fullPath) as $filename)
            {
                $domain = basename($filename, Convention::FILE_TYPE_LOCALE_FILE);
                $localeCache = Yaml::load($filename);
                $this->_localeText[$domain] = isset($localeCache) ? $localeCache : array();

                if (ENABLE_LOCALE_EXPORT)
                {
                    $this->_localePath[$domain] = $filename;
                }

                //[+]DEBUG
                $this->log()->debug("Loading locale text from file system for domain: {$domain}");
                //[-]DEBUG
            }

            $fullPath = APP_LOCALE . '/' . $this->_currentLocale . '/*' . Convention::FILE_TYPE_LOCALE_FILE;

            foreach (glob($fullPath) as $filename)
            {
                $domain = basename($filename, Convention::FILE_TYPE_LOCALE_FILE);
                $localeCache = Yaml::load($filename);
                $this->_localeText[$domain] = isset($localeCache) ? $localeCache : array();

                if (ENABLE_LOCALE_EXPORT)
                {
                    $this->_localePath[$domain] = $filename;
                }
                
                //[+]DEBUG
                $this->log()->debug("Loading locale text from file system for domain: {$domain}");
                //[-]DEBUG
            }

            if ($useCache)
            {
                $cache->set($key, serialize($this->_localeText));
            }
        }
        */
    }

    private function _loadLocale($domain)
    {
        //TODO: use cache
        $fullPath = APP_LOCALE . '/' . $this->_currentLocale . "/{$domain}" . Convention::FILE_TYPE_LOCALE_FILE;
        $this->_localeText[$domain] = file_exists($fullPath) ? Yaml::load($fullPath) : array();
    }

    private function _exportLocale($domain)
    {
        $exportPath = APP_LOCALE . '/' . $this->_currentLocale;
        $exportFile = $exportPath . '/' . $domain . Convention::FILE_TYPE_LOCALE_FILE;

        ensure_dir_exist($exportPath);

        $domainText = $this->_localeText[$domain];
        file_put_contents($exportFile, Yaml::dump($domainText), LOCK_EX);
    }
}