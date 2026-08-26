<?php

namespace Teksite\IconLaravel\Component;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Teksite\IconLaravel\Service\IconManager;

class TekIcon extends Component
{
    private IconManager $iconManager;

    public function __construct(
        public string $icon,
        public ?string $title = null,
        public ?string $type = 'outline',
        public string $viewbox = '0 0 24 24',
        public string $x = '0',
        public string $y = '0',
        public string $width = '24',
        public string $height = '24',
        public string $strokeWidth = '1',
        public string $strokeLinecap = 'round',
        public string $strokeLinejoin = 'round',
    ) {
        $this->iconManager = new IconManager();
    }

    public function render(): View|Htmlable|Closure|string
    {
        $path = $this->iconManager->getIcon($this->icon, type: $this->type, render :false);

        $defaultClass = " $this->type-icon";

        return <<<BLADE
<svg
    x="{{ \$x }}"
    y="{{ \$y }}"
    width="{{ \$width }}"
    height="{{ \$height }}"
    viewBox="{{ \$viewbox }}"
    {{ \$attributes->merge(['class' => 'tkicon ' . \$icon . '$defaultClass']) }}
    data-icon="{{ \$icon }}"
    stroke-width="{{ \$strokeWidth }}"
    stroke-linecap="{{ \$strokeLinecap }}"
    stroke-linejoin="{{ \$strokeLinejoin }}"
    xmlns="http://www.w3.org/2000/svg"
>
    @if(\$title)
        <title>{{ \$title }}</title>
    @endif

    {$path}
</svg>
BLADE;
    }
}
