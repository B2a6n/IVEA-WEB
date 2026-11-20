<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\EducandosExport;
use App\Http\Requests\EducandoDirectoryRequest;
use App\Models\User;
use App\Services\EducandoDirectoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class EducandoController extends Controller
{
    // The PDF record lists more history than the slide-over.
    private const int PDF_HISTORY_LIMIT = 100;

    public function index(EducandoDirectoryRequest $request, EducandoDirectoryService $service): View
    {
        return view('educandos.index', ['directory' => $service->directory($request->toFilters())]);
    }

    public function show(User $user, EducandoDirectoryService $service): JsonResponse
    {
        return response()->json($service->detail($user)->toArray());
    }

    public function export(EducandoDirectoryRequest $request, EducandoDirectoryService $service): BinaryFileResponse
    {
        $stamp = now()->setTimezone((string) config('ivea.reporting_timezone'))->format('Ymd-Hi');

        return Excel::download(new EducandosExport($service, $request->toFilters()), "educandos-{$stamp}.csv", ExcelFormat::CSV);
    }

    public function pdf(User $user, EducandoDirectoryService $service): Response
    {
        $detail = $service->detail($user, historyLimit: self::PDF_HISTORY_LIMIT);

        // Pinned rather than relying on package defaults: the template embeds its only image and has no PHP.
        return Pdf::setOption(['isRemoteEnabled' => false, 'isPhpEnabled' => false])
            ->loadView('educandos.pdf', ['detail' => $detail])
            ->download('ficha-'.Str::slug($user->name).'.pdf');
    }
}
