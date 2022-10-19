<?php

namespace App\Http\Controllers;

use App\Exports\BeerExport;
use App\Http\Requests\BeerRequest;
use App\Jobs\ExportJob;
use App\Jobs\SendExportEmailJob;
use App\Jobs\StoreExportDataJob;
use App\Mail\ExportEmail;
use App\Models\Export;
use App\Services\PunkapiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class BeerController extends Controller
{
    public function index(BeerRequest $request, PunkapiService $service)
    {
        return $service->getBeers(...$request->validated());
    }

    public function export(BeerRequest $request, PunkapiService $service)
    {

        $filename = 'founded-beers-' . now()->format('d-m-Y H_i') . '.xlsx';

        ExportJob::withChain([
            new SendExportEmailJob($filename),
            new StoreExportDataJob(auth()->user(), $filename)
        ])->dispatch($request->validated(), $filename);
        return 'Report created';
    }
}
