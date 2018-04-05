<?php
namespace App\Controller;

use Carbon\Carbon;
use USD\API\Provider\USDParser;
use USD\API\Model\USD;

class USDApi
{
    public static function index()
    {
        $cmd = strtolower(input('cmd'));
        switch ($cmd) {
            case 'value':
                return self::value();
            case 'list':
                return self::usds();
            case 'transform':
                return self::transform();
            case 'setup':
                return self::setup();
            case 'load':
                return self::load();
            case 'remove':
            case 'delete':
                return self::remove();
            case 'help':
            default:
                return self::help();
        }
    }
    public static function value()
    {
        $start = microtime(true);
        $tz = new \DateTimeZone(config('app.timezone'));
        $date = Carbon::parse(input('date'), $tz);
        $year = input('year');
        $month = input('month');
        $day = input('day');
        $today = Carbon::today($tz);
        if ($year or $month or $day) {
            if (!$year) {
                $year = $today->year;
            }
            if (!$month) {
                $month = $today->month;
            }
            if (!$day) {
                $day = $today->day;
            }
            $date = Carbon::createFromDate($year, $month, $day, $tz);
        }
        if (!$date) {
            $date = $today->copy();
        }
        $next_9 = $today->copy()->addMonth(1)->day(9);
        if ($date > $next_9) {
            return api(false, 204);
        } else {
            $usd = \Model::factory(USD::class)->where('fecha', $date->format('Y-m-d'))->findOne();
            if (!$usd) {
                self::load();
                $usd = \Model::factory(USD::class)->where('fecha', $date->format('Y-m-d'))->findOne();
            }
            $output = ['usd' => ['date' => $date->format('Y-m-d'), 'value' => $usd->valor], 'total' => 1];
            if (input('time') == true) {
              $output['time'] = microtime(true) - $start;
            }
            return api($output);
        }
    }
    public static function usds()
    {
        $start = microtime(true);
        $year = input('year');
        if ($year == null) {
            $tz = new \DateTimeZone(config('app.timezone'));
            $today = Carbon::today($tz);
            $year = $today->year;
        }
        $month = input('month');
        if ($month != null) {
            $usds = \Model::factory(USD::class)->whereLike('fecha', $year . '-' . $month . '%')->orderByAsc('fecha')->findMany();
        } else {
            $usds = \Model::factory(USD::class)->whereLike('fecha', $year . '%')->orderByAsc('fecha')->findMany();
        }
        if (count($usds) == 0) {
            self::load();
            if ($month != null) {
                $usds = \Model::factory(USD::class)->whereLike('fecha', $year . '-' . $month . '%')->orderByAsc('fecha')->findMany();
            } else {
                $usds = \Model::factory(USD::class)->whereLike('fecha', $year . '%')->orderByAsc('fecha')->findMany();
            }
        }
        $output = ['total' => count($usds)];
        foreach ($usds as $usd) {
            $output['usds'] []= ['date' => $usd->fecha, 'value' => $usd->valor];
        }
        if (input('time') == true) {
          $output['time'] = microtime(true) - $start;
        }
        return api($output);
    }
    public static function transform()
    {
        $start = microtime(true);
        $type = input('to');
        $value = input('value');
        $tz = new \DateTimeZone(config('app.timezone'));
        $date = Carbon::parse(input('date'), $tz);
        $year = input('year');
        $month = input('month');
        $day = input('day');

        $today = Carbon::today($tz);
        if ($year or $month or $day) {
            if (!$year) {
                $year = $today->year;
            }
            if (!$month) {
                $month = $today->month;
            }
            if (!$day) {
                $day = $today->day;
            }
            $date = Carbon::createFromDate($year, $month, $day, $tz);
        }
        if (!$date) {
            $date = $today->copy();
        }
        $next_9 = $today->copy()->addMonth(1)->day(9);
        if ($date > $next_9) {
            return api(false, 204);
        } else {
            $usd = \Model::factory(USD::class)->where('fecha', $date->format('Y-m-d'))->findOne();
            switch (strtolower($type)) {
                case 'usd':
                case 'usds':
                    $result = $value / $usd->valor;
                    break;
                case 'pesos':
                case 'clp':
                    $result = $value * $usd->valor;
                    break;
                default:
                    $result = 'Can not transform to ' . $type;
            }
            $output = [
                    'status' => 'ok',
                    'date' => $date->format('Y-m-d'),
                    'from' => $value,
                    'to' => $result
            ];
            if (input('time') == true) {
              $output['time'] = microtime(true) - $start;
            }
            return api($output);
        }
    }
    public static function help()
    {
        $output = [];
        $output['commands'] = [
                'usd' => [
                        'subcommands' => [
                                'value' => [
                                        'options' => [
                                                'date' => ['type' => 'string', 'format' => 'Y-m-d'],
                                                'year' => ['type' => 'int'],
                                                'month' => ['type' => 'int'],
                                                'day' => ['type' => 'int'],
                                                'time' => ['type' => 'bool']
                                        ],
                                        'description' => 'returns the value for USD for that date'
                                ],
                                'list' => [
                                        'options' => [
                                                'year' => ['type' => 'int'],
                                                'month' => ['type' => 'int'],
                                                'time' => ['type' => 'bool']
                                        ],
                                        'description' => 'returns all USD values for the month or year'
                                ],
                                'transform' => [
                                        'options' => [
                                                'date' => ['type' => 'string', 'format' => 'Y-m-d'],
                                                'year' => ['type' => 'int'],
                                                'month' => ['type' => 'int'],
                                                'day' => ['type' => 'int'],
                                                'to' => ['type' => 'string', 'options' => ['clp', 'usd', 'pesos', 'usds']],
                                                'value' => ['type' => 'float'],
                                                'time' => ['type' => 'bool']
                                        ],
                                        'description' => 'returns the transformation of the value to CLP or USD'
                                ],
                                'setup' => [
                                        'description' => 'loads all USD values that are missing from the database'
                                ],
                                'load' => [
                                        'options' => [
                                                'getter' => ['type' => 'string'],
                                                'year' => ['type' => 'int']
                                        ],
                                        'description' => 'loads all USD for the selected year'
                                ],
                                'remove' => [
                                        'options' => [
                                                'date' => ['type' => 'string', 'format' => 'Y-m-d'],
                                                'year' => ['type' => 'int'],
                                                'month' => ['type' => 'int'],
                                                'day' => ['type' => 'int']
                                        ],
                                        'description' => 'remove all values for date, month, year'
                                ],
                                'delete' => [
                                        'description' => 'alias of remove'
                                ],
                                'help' => [
                                        'description' => 'This help'
                                ]
                        ]
                ]
        ];
        return api($output);
    }

    public static function setup()
    {
        $start = microtime(true);
        self::loadUSD();
        return api(['status' => 'ok', 'time' => microtime(true) - $start]);
    }
    protected static function loadUSD()
    {
        $parser = new USDParser();
        $getters = $parser->listGetters();
        $parser->getAll($getters);
    }
    public static function load()
    {
        $year = input('year');
        $getter = input('getter');
        $parser = new USDParser();
        $start = microtime(true);

        $getters = [];
        if ($getter == null) {
            $getters = $parser->listGetters();
        } else {
            $getters = $parser->findGetter($getter);
        }
        if ($year == null) {
            $date = input('date');
            if ($date != null) {
                $tz = new \DateTimeZone(config('app.timezone'));
                $date = Carbon::parse($date, $tz);
                $year = $date->year;
            }
        }
        if ($year == null) {
            foreach ($getters as $getter) {
                $parser->get($getter);
            }
        } else {
            foreach ($getters as $getter) {
                $parser->getYear($getter, $year);
            }
        }
        return api(['status' => 'ok', 'getters' => count($getters), 'time' => microtime(true) - $start]);
    }
    public static function remove()
    {
        $start = microtime(true);
        $tz = new \DateTimeZone(config('app.timezone'));
        $date = input('date');
        $year = input('year');
        $month = input('month');
        $day = input('day');

        $today = Carbon::today($tz);
        if ($year or $month or $day) {
            if ($year == null) {
                $year = $today->year;
            }

            if ($month != null) {
                if ($day != null) {
                    $date = Carbon::createFromDate($year, $month, $day, $tz);
                    $next_9 = $today->copy()->addMonth(1)->day(9);
                    if ($date > $next_9) {
                        return api(false, 204);
                    } else {
                        $usds = \Model::factory(USD::class)->where('fecha', $date->format('Y-m-d'));
                    }
                } else {
                    $usds = \Model::factory(USD::class)->whereLike('fecha', $year . '-' . $month . '%')->orderByAsc('fecha');
                }
            } else {
                $usds = \Model::factory(USD::class)->whereLike('fecha', $year . '%')->orderByAsc('fecha');
            }
        } else {
            if ($date) {
                $date = Carbon::parse(input('date'), $tz);
                $next_9 = $today->copy()->addMonth(1)->day(9);
                if ($date > $next_9) {
                    return api(false, 204);
                } else {
                    $usds = \Model::factory(USD::class)->where('fecha', $date->format('Y-m-d'));
                }
            } else {
                $usds = \Model::factory(USD::class);
            }
        }
        if (count($usds) > 100) {
            set_time_limit(count($usds) * 3);
        }
        $cnt = $usds->count();
        $status = ($usds->deleteMany()) ? 'ok' : 'error';
        $output = ['status' => $status, 'total' => $cnt, 'time' => microtime(true) - $start];
        return api($output);
    }
}
