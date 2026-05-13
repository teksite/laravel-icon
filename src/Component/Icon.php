<?php

namespace Teksite\IconLaravel\Component;


use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Teksite\IconLaravel\Service\IconManager;

class Icon extends Component
{

    public function __construct(
        public IconManager $iconManager,
        public string      $icon,
        public string      $viewbox = "0 0 24 24",
        public string      $class = '',
        public string      $title = '',
        public string      $x = '0px',
        public string      $y = '0px',
        public string      $width = '24',
        public string      $height = '24',
        public string      $strokeWidth = '1',
        public string      $strokeLinecap = "round",
        public string      $strokeLinejoin = "round",
    )
    {

    }

    public function render(): View|Htmlable|\Closure|string
    {
        return <<<'blade'
<svg xmlns="http://www.w3.org/2000/svg" >
    <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
</svg>
blade;
    }
}
