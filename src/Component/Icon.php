<?php

namespace Teksite\IconLaravel\Component;


use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use Teksite\IconLaravel\Service\IconManager;

class Icon extends Component
{
    private IconManager $iconManager;

    public function __construct(
        public string  $icon,
        public ?string $title = null,
        public ?string $type = 'outline',
        public string  $viewbox = "0 0 24 24",
        public string  $x = '0px',
        public string  $y = '0px',
        public string  $width = '24',
        public string  $height = '24',
        public string  $strokeWidth = '1',
        public string  $strokeLinecap = "round",
        public string  $strokeLinejoin = "round",
    )
    {
        $this->iconManager = app(IconManager::class);
    }

    public function render(): View|Htmlable|\Closure|string
    {
        $path = $this->iconManager->getIcon($this->icon, type: $this->type, render: false);
        $defaultClass = " $this->type-icon";

        $view = config('icon-setting.component', 'components.icon');

        if (\Illuminate\Support\Facades\View::exists($view)) {
            return view($view, ['path' => $path]);
        }

        Log::error("Icon component view [$view] does not exist. so the app use fallback icon view");

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
