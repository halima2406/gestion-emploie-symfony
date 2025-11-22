<?php
namespace App\Service\Impl;

use App\Service\GenerateNumeroService;

class GenerateNumeroServiceImpl implements GenerateNumeroService{

  

    public function generateCodeEmploye(): string
    {
        return 'EMPL'.strtoupper(bin2hex(random_bytes(4)));
    }
}