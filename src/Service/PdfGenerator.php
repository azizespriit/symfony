<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGenerator
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generatePdf(string $html, string $filename): string
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Enable loading external assets (e.g., images)

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the PDF to a file
        $output = $dompdf->output();
        $pdfPath = 'uploads/pdf/' . $filename;
        $directory = dirname($pdfPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // recursive = true
        }
        file_put_contents($pdfPath, $output);

        return $pdfPath;
    }

    public function generateReclamationPdf(array $reclamations, string $filename): string
    {
        // Render the HTML for the PDF using a Twig template
        $html = $this->twig->render('reclamation/pdf.html.twig', [
            'reclamations' => $reclamations,
        ]);

        return $this->generatePdf($html, $filename);
    }
}