<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanMasterController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    public function index()
    {
        return view('laporan.index');
    }

    public function generateReport(Request $request)
    {
        $type = $request->input('type', 'stock');
        $filters = $request->all();
        $format = $request->input('format', 'json');

        $data = match ($type) {
            'stock'        => $this->reportingService->getStockReport($filters),
            'inbound'      => $this->reportingService->getInboundReport($filters),
            'outbound'     => $this->reportingService->getOutboundReport($filters),
            'mutation'     => $this->reportingService->getMutationReport($filters),
            'opname'       => $this->reportingService->getStockOpnameReport($filters),
            'maintenance'  => $this->reportingService->getMaintenanceReport($filters),
            'pasang'       => $this->reportingService->getPasangReport($filters),
            'copot'        => $this->reportingService->getCopotReport($filters),
            'warranty'     => $this->reportingService->getWarrantyReport($filters),
            'return'       => $this->reportingService->getReturnReport($filters),
            'region'       => $this->reportingService->getRegionReport(),
            'team'         => $this->reportingService->getTeamReport(),
            default        => $this->reportingService->getStockReport($filters),
        };

        // Audit export action
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['type' => $type, 'format' => $format, 'filters' => $filters])
            ->log("User " . auth()->user()->name . " melakukan EXPORT laporan [{$type}] dalam format [{$format}]");

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.laporan-generic', compact('data', 'type', 'filters'));
            return $pdf->stream("laporan-{$type}.pdf");
        }

        return response()->json([
            'success' => true,
            'type'    => $type,
            'data'    => $data
        ]);
    }
}
