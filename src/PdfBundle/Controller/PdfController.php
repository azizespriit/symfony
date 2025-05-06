<?php
namespace App\PdfBundle\Controller;

use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    #[Route('/pdf/example', name: 'pdf_example')]
    public function generateExamplePdf(): Response
    {
        $html = $this->renderView('@Pdf/example.html.twig', [
            'title' => 'Exemple de PDF',
            'content' => 'Ceci est un exemple de contenu pour un fichier PDF.',
        ]);

        $pdfContent = $this->pdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="example.pdf"',
        ]);
    }
}