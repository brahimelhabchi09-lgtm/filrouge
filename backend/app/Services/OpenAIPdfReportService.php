<?php

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Model\Meeting;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpenAIPdfReportService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY', ''));
    }

    public function generatePdfReport(Meeting $meeting, RequestMeeting $requestMeeting): ?string
    {
        try {
            $requestMeeting->load([
                'bde',
                'generatedReport.reports.student',
                'generatedReport.reports.category'
            ]);

            $content = $this->generateReportContent($meeting, $requestMeeting);
            $pdfContent = $this->generateHtmlForPdf($meeting, $requestMeeting, $content);
            
            $filename = 'meeting-reports/meeting-' . $meeting->id . '-' . Str::random(8) . '.html';
            
            if (Storage::disk('public')->put($filename, $pdfContent)) {
                return $filename;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('OpenAI PDF generation failed: ' . $e->getMessage());
            return $this->generateFallbackReport($meeting, $requestMeeting);
        }
    }

    private function generateReportContent(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        if (empty($this->apiKey)) {
            return $this->getDefaultReportContent($meeting, $requestMeeting);
        }

        try {
            $prompt = $this->buildPrompt($meeting, $requestMeeting);
            
            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional report writer. Generate a detailed, well-structured meeting report in plain text format.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.7
                ]),
                CURLOPT_TIMEOUT => 60
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                return $data['choices'][0]['message']['content'] ?? $this->getDefaultReportContent($meeting, $requestMeeting);
            }

            return $this->getDefaultReportContent($meeting, $requestMeeting);
        } catch (\Exception $e) {
            \Log::error('OpenAI API call failed: ' . $e->getMessage());
            return $this->getDefaultReportContent($meeting, $requestMeeting);
        }
    }

    private function buildPrompt(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        $bde = $requestMeeting->bde;
        $generatedReport = $requestMeeting->generatedReport;
        $reports = $generatedReport->reports ?? collect();

        $reportsList = '';
        foreach ($reports as $index => $report) {
            $reportsList .= "Report " . ($index + 1) . ":\n";
            $reportsList .= "- Category: " . ($report->category->name ?? 'Unknown') . "\n";
            $reportsList .= "- Student: " . ($report->student->first_name ?? '') . " " . ($report->student->last_name ?? '') . "\n";
            $reportsList .= "- Date: " . ($report->created_at?->format('Y-m-d H:i') ?? 'N/A') . "\n";
            $reportsList .= "- Message: " . ($report->message ?? 'No message') . "\n\n";
        }

        return "Generate a professional meeting resume report for YOUPORTS system with the following details:

MEETING INFORMATION:
- Title: " . ($meeting->title ?? 'N/A') . "
- Date: " . ($meeting->date?->format('l, F j, Y g:i A') ?? 'N/A') . "
- Google Meet Link: " . ($meeting->link ?? 'N/A') . "

BDE INFORMATION:
- Name: " . ($bde ? $bde->first_name . ' ' . $bde->last_name : 'N/A') . "
- Email: " . ($bde->email ?? 'N/A') . "

PROBLEM SUMMARY:
- Total Reports: " . ($generatedReport->reports_count ?? 0) . "
- Priority: " . strtoupper($generatedReport->priority ?? 'N/A') . "
- Status: " . strtoupper($generatedReport->status ?? 'N/A') . "
- Description: " . ($generatedReport->message ?? 'No description') . "

INDIVIDUAL REPORTS:
" . ($reportsList ?: 'No individual reports available.') . "

Please generate a well-structured, professional report that summarizes this meeting request. Include key findings, patterns identified, and recommendations if applicable. Format it clearly with sections.";
    }

    private function getDefaultReportContent(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        $bde = $requestMeeting->bde;
        $generatedReport = $requestMeeting->generatedReport;
        
        $content = "================================================================================\n";
        $content .= "                    MEETING RESUME - YOUPORTS\n";
        $content .= "================================================================================\n\n";
        
        $content .= "TITLE: " . ($meeting->title ?? 'N/A') . "\n";
        $content .= "DATE: " . ($meeting->date?->format('l, F j, Y g:i A') ?? 'N/A') . "\n";
        $content .= "MEETING LINK: " . ($meeting->link ?? 'N/A') . "\n\n";
        
        $content .= "--------------------------------------------------------------------------------\n";
        $content .= "BDE CONTACT\n";
        $content .= "--------------------------------------------------------------------------------\n";
        $content .= "Name: " . ($bde ? $bde->first_name . ' ' . $bde->last_name : 'N/A') . "\n";
        $content .= "Email: " . ($bde->email ?? 'N/A') . "\n\n";
        
        $content .= "--------------------------------------------------------------------------------\n";
        $content .= "PROBLEM SUMMARY\n";
        $content .= "--------------------------------------------------------------------------------\n";
        $content .= "Total Reports: " . ($generatedReport->reports_count ?? 0) . "\n";
        $content .= "Priority: " . strtoupper($generatedReport->priority ?? 'N/A') . "\n";
        $content .= "Description: " . ($generatedReport->message ?? 'No description available') . "\n\n";
        
        $content .= "--------------------------------------------------------------------------------\n";
        $content .= "                                    Auto-generated by YOUPORTS\n";
        $content .= "================================================================================\n";
        
        return $content;
    }

    private function generateHtmlForPdf(Meeting $meeting, RequestMeeting $requestMeeting, string $aiContent): string
    {
        $bde = $requestMeeting->bde;
        $generatedReport = $requestMeeting->generatedReport;
        $reports = $generatedReport->reports ?? collect();
        $priorityColor = $this->getPriorityColor($generatedReport->priority ?? 'P2');

        $reportsHtml = '';
        foreach ($reports as $index => $report) {
            $reportsHtml .= '
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' . ($report->category->name ?? 'Unknown') . '</td>
                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' . ($report->student->first_name ?? '') . ' ' . ($report->student->last_name ?? '') . '</td>
                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' . ($report->created_at?->format('M j, Y') ?? 'N/A') . '</td>
                <td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' . nl2br(htmlspecialchars($report->message ?? 'No message')) . '</td>
            </tr>';
        }

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meeting Report - ' . htmlspecialchars($meeting->title ?? 'YOUPORTS Meeting') . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; line-height: 1.6; color: #1e293b; padding: 0; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 8px; font-weight: 700; }
        .header p { font-size: 14px; opacity: 0.95; }
        .content { padding: 30px; background: #f8fafc; }
        .section { margin-bottom: 30px; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .section-title { font-size: 13px; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #6366f1; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .info-box { background: #f1f5f9; padding: 12px 15px; border-radius: 8px; }
        .info-box label { font-size: 9px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
        .info-box p { font-size: 12px; color: #1e293b; }
        .priority { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; color: white; }
        .description-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 0 8px 8px 0; margin-top: 10px; }
        .description-box p { color: #92400e; }
        .ai-content { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 20px; border-radius: 8px; white-space: pre-wrap; font-size: 11px; line-height: 1.8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e293b; color: white; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-size: 10px; }
        .footer { text-align: center; padding: 25px; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; margin-top: 30px; }
        @media print { body { padding: 0; } .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; } .section { box-shadow: none; } }
        @page { margin: 15mm; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>YOUPORTS Meeting Report</h1>
            <p>' . htmlspecialchars($meeting->title ?? 'Meeting Request') . '</p>
        </div>
        
        <div class="content">
            <div class="section">
                <div class="section-title">Meeting Information</div>
                <div class="info-grid">
                    <div class="info-box">
                        <label>Meeting ID</label>
                        <p>#' . $meeting->id . '</p>
                    </div>
                    <div class="info-box">
                        <label>Date & Time</label>
                        <p>' . ($meeting->date?->format('l, F j, Y g:i A') ?? 'N/A') . '</p>
                    </div>
                    <div class="info-box">
                        <label>Meet Link</label>
                        <p><a href="' . htmlspecialchars($meeting->link ?? '#') . '" style="color: #6366f1;">' . htmlspecialchars($meeting->link ?? 'N/A') . '</a></p>
                    </div>
                    <div class="info-box">
                        <label>Reports Count</label>
                        <p><span class="priority" style="background: ' . $priorityColor . ';">' . strtoupper($generatedReport->priority ?? 'N/A') . '</span> - ' . ($generatedReport->reports_count ?? 0) . ' reports</p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">BDE Contact</div>
                <div class="info-grid">
                    <div class="info-box">
                        <label>Name</label>
                        <p>' . ($bde ? htmlspecialchars($bde->first_name . ' ' . $bde->last_name) : 'N/A') . '</p>
                    </div>
                    <div class="info-box">
                        <label>Email</label>
                        <p>' . htmlspecialchars($bde->email ?? 'N/A') . '</p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Problem Summary</div>
                <div class="info-box" style="margin-bottom: 15px;">
                    <label>AI-Generated Analysis</label>
                </div>
                <div class="ai-content">' . nl2br(htmlspecialchars($aiContent)) . '</div>
                
                <div class="description-box" style="margin-top: 20px;">
                    <label style="font-size: 10px; font-weight: 600; color: #92400e; text-transform: uppercase;">Original Description</label>
                    <p style="margin-top: 5px;">' . nl2br(htmlspecialchars($generatedReport->message ?? 'No description provided')) . '</p>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Individual Reports (' . $reports->count() . ')</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 18%;">Category</th>
                            <th style="width: 22%;">Student</th>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 48%;">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . ($reportsHtml ?: '<tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">No individual reports available</td></tr>') . '
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>YOUPORTS</strong> - Automated Report Generation System</p>
            <p>Generated: ' . now()->format('F j, Y g:i A') . '</p>
            <p style="margin-top: 10px; font-size: 9px;">Print this page as PDF: File → Print → Save as PDF</p>
        </div>
    </div>
</body>
</html>';
    }

    private function generateFallbackReport(Meeting $meeting, RequestMeeting $requestMeeting): ?string
    {
        return $this->generateHtmlForPdf($meeting, $requestMeeting, $this->getDefaultReportContent($meeting, $requestMeeting));
    }

    private function getPriorityColor(string $priority): string
    {
        return match(strtoupper($priority)) {
            'P0' => '#ef4444',
            'P1' => '#f59e0b',
            'P2' => '#3b82f6',
            default => '#6366f1'
        };
    }
}
