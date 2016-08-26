<?php
namespace Autohome;

/**
 * TimeLine Controller Script
 */
class Timeline
{
    const TIMEZONE_DEFAULT = 'Europe/Brussels';
    const TODAY_FILES_PATH = '/tmp';

    const TIME_DAWN = 'twilight_begin';
    const TIME_SUNRISE = 'sunrise';
    const TIME_NOON = 'noon';
    const TIME_SUNSET = 'sunset';
    const TIME_DUSK = 'twilight_end';
    const TIME_MIDNIGHT = 'midnight';
    const TIME_ALWAYS = 'always';

    protected $path;
    protected $longitude;
    protected $latitude;
    protected $options;
    protected $plugins;

    protected static $instance = null;

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * Get the instance of Timeline Controller
     *
     * @param array $options
     * @return self
     */
    public static function load($options=[])
    {
        $instance = static::$instance ?: new static;
        return $instance->init($options);
    }

    /**
     * Initiate the Timeline Controller with options
     * and grap day information from API or cached file
     *
     * @param array $options
     * @return self
     */
    public function init($options=[])
    {
        $this->path = isset($options['path'])
            ? $options['path']
            : self::TODAY_FILES_PATH;

        $this->longitude = isset($options['lng'])
            ? $options['lng']
            : 0.0;

        $this->latitude = isset($options['lat'])
            ? $options['lat']
            : 0.0;

        $this->plugins = [];
        $this->options = array_merge(
            $this->loadDatas(isset($options['clear']) && $options['clear']),
            $options
        );

        return $this;
    }

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * Start the timeline pasring
     *
     * @param array $timedActions
     */
    public function start($timedActions = [])
    {
        $timeline = $this;
        array_walk($timedActions, function($actions, $time) use ($timeline) {

            if (!$this->isActive($actions)) { return false; }

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
                    $timeline->isTime($time) && $timeline->execute($actions);
            }
            return false;
        });
    }

    // ----------------------------------------------------------------------------------------------------------------

    public function isActive(&$actions)
    {
        if (isset($actions['disable'])) {
            if(false !== strpos('yes oui 1', strtolower($actions['disable']))) {
                return false;
            }
            unset($actions['disable']);
        }

        if(isset($actions['days'])) {
            if(!in_array(strtolower(date('D')), explode(' ', strtolower($actions['days'])))) {
                return false;
            }
            unset($actions['days']);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDawn()
    {
        return self::isTime($this->options[ self::TIME_DAWN ]);
    }

    /**
     * @return bool
     */
    public function isSunrise()
    {
        return self::isTime($this->options[ self::TIME_SUNRISE ]);
    }

    /**
     * @return bool
     */
    public function isSunset()
    {
        return self::isTime($this->options[ self::TIME_SUNSET ]);
    }

    /**
     * @return bool
     */
    public function isDusk()
    {
        return self::isTime($this->options[ self::TIME_DUSK]);
    }

    /**
     * @return bool
     */
    public function isMidnight()
    {
        return self::isTime( (new \DateTime)->setTime(0, 0) );
    }

    /**
     * @param $time
     * @return bool
     */
    public function isTime($time)
    {
        $time = is_string($time)
            ? \DateTime::createFromFormat('H:i', $time)
            : $time;
        $now = (new \DateTime)->format('H:i');
        $time = $time->format('H:i');

        return $time == $now;
    }

    // ================================================================================================================

    /**
     * @param bool $useCache Try to use datas from file or make Api call
     * @return array
     */
    private function loadDatas($useCache = false)
    {
        $todayFile = sprintf('%s/%s.json', $this->path, date('Ymd')) ;
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
     * @return array
     */
    private function execute($actions = [])
    {
        $instance = $this;

        return array_filter(array_map(function($plugin, $action) use ($instance) {
            if ($plugin = $instance->registerPlugin($plugin)) {
                return $plugin->execute($action);
            }
            return null;
        }, array_keys($actions), $actions));
    }

    /**
     * Create and register plugin according his name
     *
     * @param $pluginName
     * @return null|Plugins\PluginInterface
     */
    public function registerPlugin($pluginName)
    {
        if(array_key_exists($pluginName, $this->plugins)) {
            return $this->plugins[$pluginName];
        }

        list($vendor, $plugin) = explode('_', $pluginName);
        $pluginClass = sprintf('\Autohome\Plugins\%s\%sPlugin', ucfirst($vendor), ucfirst($plugin)) ;

        /* @var Plugins\PluginInterface $pluginClass */
        if(class_exists($pluginClass) && in_array(Plugins\PluginInterface::class, class_implements($pluginClass))) {
            $this->plugins[$pluginName] = new $pluginClass($this->options[$vendor]);

            return $this->plugins[$pluginName];
        }

        return null;
    }
}
