<?php

namespace PharmaFEFO\Controller\Web;

class DashboardController
{
    public function index(): void
    {
        require __DIR__ . '/../../../templates/layout/base.php';
    }
}
