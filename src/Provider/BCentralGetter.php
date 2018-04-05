<?php
namespace USD\API\Provider;

use USD\API\Definition\Getter;

class BCentralGetter implements Getter
{
    protected $getter;
    protected $url;
    protected $params;
    protected $vars;

    public function __construct()
    {
        $this->getter = 'bcentral';
        $this->url = config('getters.' . $this->getter . '.url');
        $this->params = config('getters.' . $this->getter . '.params');
        $this->vars = [];
        if (count(config('getters.' . $this->getter . '.variables')) > 0) {
          $this->vars = array_combine(config('getters.' . $this->getter . '.variables'), array_fill(0, count(config('getters.' . $this->getter . '.variables')) - 1, 0));
        }
    }
    public function get(int $year)
    {
        $query = array_merge($this->params, $this->vars);

        $result = file_get_contents($this->url);
        d($result);
    }
}
?>
