<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class PartnershipTemplatePdfService
{
    public function download(): Response
    {
        $html = View::make('pdf.partnership-application-template', [
            'headerPath' => $this->headerDataUri(),
            'generatedAt' => now(),
        ])->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->setChroot(public_path());
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Template-Pengajuan-Kerjasama-Poltekkes-Banten.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function headerDataUri(): ?string
    {
        $candidates = [
            public_path('images/hero/header.png'),
            public_path('images/branding/logo-poltekkes.png'),
        ];

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }

            $mime = @mime_content_type($path) ?: 'image/png';
            $data = base64_encode((string) file_get_contents($path));

            return 'data:'.$mime.';base64,'.$data;
        }

        return null;
    }
}
