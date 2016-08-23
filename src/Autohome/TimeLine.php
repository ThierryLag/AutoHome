<?php
namespace Autohome;

/**
 * TimeLine Controller Script
 */
class Timeline
{
    const TIMEZONE_DEFAULT = 'Europe/Brussels';
    const TODAY_FILES_PATH = '/tmp';

    const TIME_TWILIGHT_BEGIN = 'twilight_begin';
    const TIME_SUNRISE = 'sunrise';
    const TIME_NOON = 'noon';
    const TIME_SUNSET = 'sunset';
    const TIME_TWILIGHT_END = 'twilight_end';
    const TIME_MIDNIGHT = 'midnight';

    protected $path;
    protected $longitude;
    protected $latitude;
    protected $datas;

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

        $reloadCache = isset($options['clear']) && $options['clear'];
        $this->datas = $this->loadDatas($reloadCache);

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
            $time = trim(strtolower($time));

            switch ($time) {
                case self::TIME_SUNRISE:
                    $timeline->isSunrise() && (boolean) $timeline->execute($actions);
                    break;

                case self::TIME_SUNSET:
                    $timeline->isSunset() && (boolean) $timeline->execute($actions);
                    break;

                case self::TIME_MIDNIGHT:
                    $timeline->isMidnight() && (boolean) $timeline->execute($actions);
                    break;

                default:
                    $timeline->isTime($time) && (boolean) $timeline->execute($actions);
            }
        });
    }

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * @return bool
     */
    public function isSunrise()
    {
        return self::isTime($this->datas[ self::TIME_SUNRISE ]);
    }

    /**
     * @return bool
     */
    public function isSunset()
    {
        return self::isTime($this->datas[ self::TIME_SUNSET ]);
    }

    /**
     * @return bool
     */
    public function isMidnight()
    {
        return self::isTime( (new \DateTime(null, self::TIMEZONE_DEFAULT))->setTime(0, 0) );
    }

    /**
     * @param $time
     * @return bool
     */
    public function isTime($time)
    {
        $time = is_string($time)
            ? \DateTime::createFromFormat('H:i', $time, new \DateTimeZone(self::TIMEZONE_DEFAULT))
            : $time;
        $now = (new \DateTime(null, $time->getTimezone()) )->format('H:i');
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

        if ($useCache && file_exists($todayFile)) {
            $datas = (array) json_decode(file_get_contents($todayFile));
            array_walk($datas, function(&$date) {
                $date = new \DateTime($date->date, new \DateTimeZone('UTC'));
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
            self::TIME_TWILIGHT_BEGIN   => $this->fromUtcDate($callDatas->results->civil_twilight_begin),
            self::TIME_SUNRISE          => $this->fromUtcDate($callDatas->results->sunrise),
            self::TIME_NOON             => $this->fromUtcDate($callDatas->results->solar_noon),
            self::TIME_SUNSET           => $this->fromUtcDate($callDatas->results->sunset),
            self::TIME_TWILIGHT_END     => $this->fromUtcDate($callDatas->results->civil_twilight_end),
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
        )->setTimezone(new \DateTimeZone(self::TIMEZONE_DEFAULT));
    }

    /**
     * Loop over the actions match the current time
     * and call the plugin to execute the action
     */
    private function execute($actions = [])
    {
        return array_filter(array_map(function($action) {
            list($vendor, $plugin) = explode('_', $action['plugin']);
            $pluginClass = sprintf('\Autohome\Plugins\%s\%sPlugin', ucfirst($vendor), ucfirst($plugin)) ;

            /* @var Plugins\PluginInterface $pluginClass */
            return class_exists($pluginClass)
                && in_array(Plugins\PluginInterface::class, class_implements($pluginClass))
                && $pluginClass::execute($action);
        }, $actions));
    }
}
