<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class PdfExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to PDF format.
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata (survey ID, language, etc.)
     * @return array Export result with file path and metadata
     * @throws RuntimeException If file cannot be created
     */
    public function export(array $responses, array $surveyQuestions, array $metadata): array
    {
        $surveyId = $metadata['surveyId'];
        $timestamp = date('YmdHis');

        $tempDir = sys_get_temp_dir();
        $filename = "survey_{$surveyId}_responses_{$timestamp}.pdf";
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        // Generate HTML content first
        $html = $this->generateHtmlContent($responses, $surveyQuestions, $metadata);

        // Convert HTML to PDF using simple PDF generation
        // For a production environment, you would use a library like TCPDF, mPDF, or Dompdf
        // For now, we'll create a basic PDF structure
        $this->createPdfFromHtml($filePath, $html, $surveyId);

        return [
            'filePath' => $filePath,
            'filename' => $filename,
            'mimeType' => $this->getMimeType(),
            'extension' => $this->getFileExtension(),
            'size' => filesize($filePath),
            'responseCount' => count($responses)
        ];
    }

    /**
     * Generate HTML content for PDF.
     *
     * @param array $responses
     * @param array $surveyQuestions
     * @param array $metadata
     * @return string
     */
    private function generateHtmlContent(array $responses, array $surveyQuestions, array $metadata): string
    {
        $surveyId = $metadata['surveyId'];

        $html = '<html><head><style>';
        $html .= 'body { font-family: Arial, sans-serif; font-size: 10pt; }';
        $html .= 'h1 { font-size: 18pt; color: #333; margin-bottom: 10px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 9pt; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }';
        $html .= 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
        $html .= '.meta { font-size: 9pt; color: #666; margin-bottom: 10px; }';
        $html .= '</style></head><body>';

        $html .= '<h1>Survey ' . htmlspecialchars($surveyId) . ' - Response Export</h1>';
        $html .= '<div class="meta">Export Date: ' . date('Y-m-d H:i:s') . '</div>';
        $html .= '<div class="meta">Total Responses: ' . count($responses) . '</div>';

        $html .= '<table>';

        // Header row
        $html .= '<tr>';
        $html .= '<th>ID</th><th>Submit Date</th><th>Last Page</th><th>Language</th><th>Seed</th>';
        foreach ($surveyQuestions as $fieldCode => $question) {
            $qid = $question['qid'];
            $html .= '<th>Q' . htmlspecialchars($qid) . '</th>';
        }
        $html .= '</tr>';

        // Response rows
        foreach ($responses as $response) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($response['id'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['submitdate'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['lastpage'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['startlanguage'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['seed'] ?? '') . '</td>';

            foreach ($surveyQuestions as $fieldCode => $question) {
                $qid = $question['qid'];
                $value = '';

                if (isset($response['answers'])) {
                    foreach ($response['answers'] as $answer) {
                        if (isset($answer['qid']) && $answer['qid'] == $qid) {
                            $value = $answer['value'] ?? '';
                            break;
                        }
                    }
                }

                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }

    /**
     * Create a basic PDF from HTML content.
     * This is a placeholder implementation that creates a simple text-based PDF.
     * In production, you should use a proper PDF library like TCPDF, mPDF, or Dompdf.
     *
     * @param string $filePath
     * @param string $html
     * @param int $surveyId
     * @throws RuntimeException
     */
    private function createPdfFromHtml(string $filePath, string $html, int $surveyId): void
    {
        // Create a basic PDF structure
        // This is a minimal PDF - for production use a proper library
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 5 0 R >>\nendobj\n";
        $pdf .= "4 0 obj\n<< /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >>\nendobj\n";

        // Extract text content from HTML for PDF
        $textContent = strip_tags($html);
        $textContent = "Survey {$surveyId} Response Export\n\n" . $textContent;
        $textContent = substr($textContent, 0, 1000); // Limit content for basic PDF

        $streamContent = "BT\n/F1 12 Tf\n50 700 Td\n";
        $lines = explode("\n", $textContent);
        $y = 700;
        foreach (array_slice($lines, 0, 40) as $line) { // Limit to 40 lines
            $line = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $line);
            $streamContent .= "({$line}) Tj\n";
            $y -= 15;
            $streamContent .= "0 -15 Td\n";
        }
        $streamContent .= "ET";

        $streamLength = strlen($streamContent);
        $pdf .= "5 0 obj\n<< /Length {$streamLength} >>\nstream\n{$streamContent}\nendstream\nendobj\n";

        $pdf .= "xref\n0 6\n0000000000 65535 f\n";
        $pdf .= "0000000009 00000 n\n";
        $pdf .= "0000000056 00000 n\n";
        $pdf .= "0000000115 00000 n\n";
        $pdf .= "0000000214 00000 n\n";
        $pdf .= "0000000303 00000 n\n";
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . (strlen($pdf) + 20) . "\n%%EOF";

        if (file_put_contents($filePath, $pdf) === false) {
            throw new RuntimeException("Unable to create export file: $filePath");
        }
    }

    /**
     * Get the file extension for PDF format.
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'pdf';
    }

    /**
     * Get the MIME type for PDF format.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/pdf';
    }
}
