<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ReportStatCard extends Component
{
    public function __construct(
        public string $label = '',
        public string $value = '',
        public string $icon = 'fa-chart-line',
        public string $iconColor = 'sky'
    ) {}

    public function render()
    {
        return view('components.report-stat-card');
    }
}
