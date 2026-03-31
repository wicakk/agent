<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class NavIcon extends Component
{
    public function __construct(
        public string $name,
        public bool $active = false
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.nav-icon');
    }
}
