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
                    $timeline->isSunrise() && $timeline->execute($actions);
                    break;

                case self::TIME_SUNSET:
                    $timeline->isSunset() && $timeline->execute($actions);
                    break;

                case self::TIME_MIDNIGHT:
                    $timeline->isMidnight() && $timeline->execute($actions);
                    break;

                default:
                    $timeline->isTime($time) && $timeline->execute($actions);
            }
        });
    }

    // ----------------------------------------------------------------------------------------------------------------

    public function isSunrise()
    {
        return self::isTime($this->datas[ self::TIME_SUNRISE ]);
    }

    public function isSunset()
    {
        return self::isTime($this->datas[ self::TIME_SUNSET ]);
    }

    public function isMidnight()
    {
        return self::isTime( (new \DateTime(null, self::TIMEZONE_DEFAULT))->setTime(0, 0) );
    }

    public function isTime($time)
    {
        $time = is_string($time)
            ? \DateTime::createFromFormat('H:i', $time, new \DateTimeZone(self::TIMEZONE_DEFAULT))
            : $time;
        $now = (new \DateTime(null, $time->getTimezone()) )->format('H:i');
        $time = $time->format('H:i');

        //printf("Time %s <> %s is %s\n", $now, $time, ($time == $now) ? 'NOW':'NOT now');

        return $time == $now;
    }

    // ================================================================================================================

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
            self::TIME_SUNSET          => $this->fromUtcDate($callDatas->results->sunset),
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

    private function execute($actions = [])
    {
        var_dump($actions);
        return true;
    }
}
