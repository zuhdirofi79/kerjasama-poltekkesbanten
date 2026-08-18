<?php

namespace App\Http\Controllers;

use App\Services\PartnershipTemplatePdfService;
use Illuminate\Http\Response;

class PartnershipApplicationTemplateController extends Controller
{
    public function __invoke(PartnershipTemplatePdfService $service): Response
    {
        return $service->download();
    }
}
