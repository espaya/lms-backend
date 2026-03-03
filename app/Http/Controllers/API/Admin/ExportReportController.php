<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;

class ExportReportController extends Controller
{
    public function exportPDF(Request $request)
    {
        try {
            $reports = $request->input('reports', []);

            if (!is_array($reports)) {
                $decoded = json_decode($reports, true);
                $reports = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
            }

            // Normalize signatures -> convert URL to relative storage path
            foreach ($reports as &$report) {
                if (!empty($report['signature']) && $report['signature'] !== 'undefined') {
                    // Extract only the filename from the URL
                    $filename = basename($report['signature']); // e.g. signature_1_1754948567.png
                    $report['signature_path'] = public_path("storage/signature/" . $filename);
                } else {
                    $report['signature_path'] = null;
                }
            }

            $pdf = Pdf::loadView('pdf.report', compact('reports'));

            return $pdf->download('report.pdf');
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            return response()->json(['messag' => 'An unexpected error occurred'], 500);
        }
    }
}
