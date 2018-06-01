<?php
namespace Autohome;

use Autohome\Plugins\PluginException;
use \M1\Vars\Vars;

/**
 * TimeLine Controller Script
 */
class Timeline
{
    const TIMEZONE_DEFAULT = 'Europe/Brussels';
    const TODAY_FILES_PATH = '../../config/days';
    const CONFIG_FIlES_PATH = '../../config';

    const TIME_DAWN = 'dawn';
    const TIME_SUNRISE = 'sunrise';
    const TIME_NOON = 'noon';
    const TIME_SUNSET = 'sunset';
    const TIME_DUSK = 'dusk';
    const TIME_MIDNIGHT = 'midnight';
    const TIME_ALWAYS = 'always';
    const TIME_WAKEUP = 'wakeup';

    protected $configPath;
    protected $path;
    protected $longitude;
    protected $latitude;
    protected $options;
    protected $plugins;
    protected $debug;
    protected $actions;

    protected static $instance = null;

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * Get the instance of Timeline Controller
     *
     * @param array|string $options
     * @return self
     */
    public static function load($options=[])
    {
        if (!static::$instance) {
            static::$instance = (new static)->init($options);
        }
        return static::$instance;
    }

    /**
     * * Initiate the Timeline Controller with options
     * and grap day information from API or cached file
     *
     * @param mixed $options
     * @return $this
     */
    private function init($options=false)
    {
        $this->configPath = self::CONFIG_FIlES_PATH;
        if (is_string($options)) {
            $this->configPath = pathinfo($options)['dirname'];
            $options = $this->loadConfigFromFile($options);
        }
        elseif (!is_array($options)) {
            $options = $this->loadConfigFromFile($this->configPath . '/app.yml');
        }

        // Default Parameters
        $options = array_merge([
            'path' => self::TODAY_FILES_PATH,
            'lng' => 0.0,
            'lat' => 0.0,
            'timeline' => '/timeline.yml',
            'debug' => false
        ], $options);

        $this->plugins = [];
        $this->path = $options['path'];
        $this->longitude = $options['lng'];
        $this->latitude = $options['lat'];
        $this->debug = $options['debug'];
        $this->options = array_merge($this->loadDatas(isset($options['clear']) && $options['clear']), $options);
        $this->actions = $this->loadConfigFromFile($this->configPath . '/' . $options['timeline']);

        return $this;
    }

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * Start the timeline pasring*
     */
    public function start()
    {
        try {
            $timeline = $this;

            array_walk($this->actions, function($actions, $time) use ($timeline) {
                if (!$this->isActive($actions) || !$this->isCurrentDay($actions)) {
                    return false;
                }

                $time = trim(strtolower($time));
                switch ($time) {
                    case self::TIME_ALWAYS:
                        $timeline->execute($actions);
                        break;

                    case self::TIME_DAWN:
                        $timeline->isDawn() && $timeline->execute($actions);
                        break;

                    case self::TIME_SUNRISE:
                        $timeline->isSunrise() && $timeline->execute($actions);
                        break;

                    case self::TIME_SUNSET:
                        $timeline->isSunset() && $timeline->execute($actions);
                        break;

                    case self::TIME_DUSK:
                        $timeline->isDusk() && $timeline->execute($actions);
                        break;

                    case self::TIME_MIDNIGHT:
                        $timeline->isMidnight() && $timeline->execute($actions);
                        break;

                    default:
                        $time = $timeline->parseTime($time);
                        if(is_array($time)) {
                            $range = $timeline->getDelta($time[0], $time[1]);
                            $delta = $timeline->getDelta($time[0]);
                            $percent = max(1 , min(100, floor(100 * $delta / $range)));

                            $timeline->inRange($time) && $timeline->execute($actions, [
                                'range' => $range,
                                'delta' => $delta,
                                'percent' => $percent
                            ]);
                        }
                        else {
                            $timeline->isTime($time) && $timeline->execute($actions);
                        }
                }
                return false;
            });
        }
        catch (PluginException $e) {
            echo 'ERROR: ', $e->getFile(), ' : ', $e->getMessage(), PHP_EOL;
        }
    }

    public function test($time)
    {
        $this->debug = $time;
        $this->start();
    }

    // ================================================================================================================

    private function loadConfigFromFile($filePath)
    {
        try {
            $config = new Vars($filePath, ['cache' => false]);
            return $config->getContent();
        }
        catch (\Exception $e) {
            echo 'File not found : ', $e->getFile(), ' : ', $e->getMessage(), PHP_EOL;
        }

        return [];
    }

    // ================================================================================================================


    /**
     * @param array $actions
     * @return bool
     */
    private function isActive(&$actions)
    {
        return $this->validate($actions, 'disable', function($action) {
            return false === strpos('yes oui 1', strtolower($action));
        });
    }

    /**
     * @param array $actions
     * @return bool
     */
    private function isCurrentDay(&$actions)
    {
        return $this->validate($actions, 'days', function($action) {
            return in_array(strtolower(date('D')), explode(' ', strtolower($action)));
        });
    }

    /**
     * @return bool
     */
    private function isDawn()
    {
        return isset($this->options[ self::TIME_DAWN ])
            && self::isTime($this->options[ self::TIME_DAWN ]);
    }

    /**
     * @return bool
     */
    private function isSunrise()
    {
        return isset($this->options[ self::TIME_SUNRISE ])
            && self::isTime($this->options[ self::TIME_SUNRISE ]);
    }

    /**
     * @return bool
     */
    private function isSunset()
    {
        return isset($this->options[ self::TIME_SUNSET ])
            && self::isTime($this->options[ self::TIME_SUNSET ]);
    }

    /**
     * @return bool
     */
    private function isDusk()
    {
        return isset($this->options[ self::TIME_SUNSET ])
            && self::isTime($this->options[ self::TIME_DUSK ]);
    }

    /**
     * @return bool
     */
    private function isMidnight()
    {
        return isset($this->options[ self::TIME_SUNSET ])
            && self::isTime( (new \DateTime)->setTime(0, 0) );
    }

    private function parseTime($time)
    {
        $parser = function($time) {
            return \DateTime::createFromFormat('H:i', trim($time));
        };

        if(strpos($time, '-')) {
            return array_map($parser, explode('-', $time));
        }
        else {
            return $parser($time);
        }
    }

    /**
     * @param $time
     * @return bool
     */
    private function isTime($time)
    {
        if($time instanceof \DateTime) {
            $time = $time->format('H:i');
        }

        $now = $this->debug instanceof \DateTime
                ? $this->debug->format('H:i')
                : ( $this->debug ?: (new \DateTime)->format('H:i') );

        return $time == $now;
    }

    private function inRange($start, $end=null)
    {
        if (is_array($start) && !$end) { list($start, $end) = $start; }

        $now = isset($this->options['debug']['time'])
            ? \DateTime::createFromFormat('H:i', $this->options['debug']['time'])
            : new \DateTime();

        return ($start <= $now && $now <= $end);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return int
     */
    private function getDelta($from, $to=null)
    {
        $to = $to ?: new \DateTime();
        $interval = $from->diff($to);
        return 60 * $interval->format('%h') + $interval->format('%i');
    }

    // ================================================================================================================

    private function validate(&$actions, $test, $callback)
    {
        foreach ($actions as $index => $action) {
            if (isset($action[$test])) {
                if(!$callback($action[$test])) {
                    return false;
                }
                unset($actions[$index]);
            }
        }
        return true;
    }

    /**
     * @param bool $useCache Try to use datas from file or make Api call
     * @return array
     */
    private function loadDatas($useCache = false)
    {
        $todayFile = sprintf('%s/%s.json', realpath($this->path), date('Ymd')) ;
        $timeZone = (new \DateTime())->getTimezone();

        if ($useCache && file_exists($todayFile)) {
            $datas = (array) json_decode(file_get_contents($todayFile));
            array_walk($datas, function(&$date) use ($timeZone) {
                $date = new \DateTime($date->date, $timeZone);
            });
        }
        else {
            $datas = $this->callApi();
            file_put_contents($todayFile, json_encode($datas));
        }

        return $datas;
    }

    private function callApi()
    {
        $callUrl = sprintf('http://api.sunrise-sunset.org/json?lat=%f&lng=%f&date=today', $this->latitude, $this->longitude);
        $callDatas = json_decode(self::curlCall($callUrl));

        return $callDatas->status == 'OK' ? [
            self::TIME_DAWN     => $this->fromUtcDate($callDatas->results->civil_twilight_begin),
            self::TIME_SUNRISE  => $this->fromUtcDate($callDatas->results->sunrise),
            self::TIME_NOON     => $this->fromUtcDate($callDatas->results->solar_noon),
            self::TIME_SUNSET   => $this->fromUtcDate($callDatas->results->sunset),
            self::TIME_DUSK     => $this->fromUtcDate($callDatas->results->civil_twilight_end),
        ] : [];
    }

    private static function curlCall($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function fromUtcDate($dateString)
    {
        return \DateTime::createFromFormat(
            'h:i:s a',
            strtolower($dateString),
            new \DateTimeZone('UTC')
        )->setTimezone( (new \DateTime)->getTimezone() );
    }

    /**
     * Loop over the actions match the current time
     * and call the plugin to execute the action
     *
     * @param array $actions
     * @param array $options
     * @return array
     */
    private function execute($actions = [], $options = [])
    {
        $instance = $this;

        return array_filter(array_map(function($action) use ($instance, $options) {
            if($instance->debug) {
                $status = $instance->valid($action['action']) ? 'Execute' : 'Invalid condition for';
                echo $instance->debug, ' - ', $status, ' : ', $action['action'], PHP_EOL;

                return false;
            }

            if ($instance->valid($action['action']) && $plugin = $instance->registerPlugin($action['action'])) {
                return $plugin->execute(array_merge($action, $options));
            }

            return false;
        }, $actions));
    }

    /**
     * Create and register plugin according his name
     *
     * @param $pluginName
     * @return null|Plugins\PluginInterface
     */
    private function registerPlugin($pluginName)
    {
        if(array_key_exists($pluginName, $this->plugins)) {
            return $this->plugins[$pluginName];
        }

        list($vendor, $plugin) = explode('_', $pluginName);
        $pluginClass = sprintf('\Autohome\Plugins\%s\%sPlugin', ucfirst($vendor), ucfirst($plugin ?: $vendor)) ;

        /* @var Plugins\PluginInterface $pluginClass */
        if(class_exists($pluginClass) && in_array(Plugins\PluginInterface::class, class_implements($pluginClass))) {
            $this->plugins[$pluginName] = new $pluginClass(
                isset($this->options[$vendor]) ? $this->options[$vendor] : []
            );

            return $this->plugins[$pluginName];
        }

        return null;
    }

    /**
     * Validate the action execution ; make test if we have a condition "IF" in the definition
     *
     * @param $action
     *
     * @return bool
     */
    private function valid($action)
    {
        $matches=[];
        if (isset($action['if'])) {
            preg_match("/(.*)([<!=>]{1,2})(.*)/", $action['if'], $matches);

            // TODO...
        }
        return true;
    }
}
