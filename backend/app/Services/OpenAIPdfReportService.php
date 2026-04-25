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
                'generatedReport.reports.category',
            ]);

            $aiContent = $this->generateAiContent($meeting, $requestMeeting);
            $html      = $this->buildHtml($meeting, $requestMeeting, $aiContent);

            $filename = 'meeting-reports/meeting-' . $meeting->id . '-' . Str::random(8) . '.html';

            Storage::disk('public')->put($filename, $html);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function generateAiContent(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        if (empty($this->apiKey) || $this->apiKey === 'your_openai_api_key_here') {
            return $this->fallbackContent($meeting, $requestMeeting);
        }

        try {
            $prompt = $this->buildPrompt($meeting, $requestMeeting);

            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model'       => 'gpt-4o-mini',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'You are a professional report writer for a school issue management system called YOUPORTS. Write concise, structured summaries.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'max_tokens'  => 1500,
                    'temperature' => 0.5,
                ]),
            ]);

            $response = curl_exec($ch);
            $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && $response) {
                $data = json_decode($response, true);
                return $data['choices'][0]['message']['content'] ?? $this->fallbackContent($meeting, $requestMeeting);
            }
        } catch (\Exception $e) {
            \Log::error('OpenAI call failed: ' . $e->getMessage());
        }

        return $this->fallbackContent($meeting, $requestMeeting);
    }

    private function buildPrompt(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        $gr      = $requestMeeting->generatedReport;
        $reports = $gr->reports ?? collect();
        $bde     = $requestMeeting->bde;

        $reportLines = $reports->map(function ($r, $i) {
            return sprintf(
                "Report %d: Category=%s | Student=%s %s | Description=%s",
                $i + 1,
                $r->category->name ?? 'Unknown',
                $r->student->first_name ?? '',
                $r->student->last_name ?? '',
                $r->description ?? 'N/A'
            );
        })->implode("\n");

        return "Generate a professional meeting summary report for YOUPORTS school issue management system.

MEETING: {$meeting->title}
DATE: {$meeting->date?->format('l, F j, Y g:i A')}
GOOGLE MEET: {$meeting->link}

BDE CONTACT: {$bde?->first_name} {$bde?->last_name} ({$bde?->email})

ISSUE SUMMARY:
- Total reports grouped: {$gr->reports_count}
- Priority: {$gr->priority}
- Description: {$gr->message}

INDIVIDUAL REPORTS:
{$reportLines}

Write a structured summary with: Executive Summary, Key Issues Identified, Affected Students, Priority Assessment, and Recommended Actions. Be concise and professional.";
    }

    private function fallbackContent(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        $gr      = $requestMeeting->generatedReport;
        $reports = $gr->reports ?? collect();
        $bde     = $requestMeeting->bde;

        $lines = "EXECUTIVE SUMMARY\n";
        $lines .= "This meeting was requested by BDE member {$bde?->first_name} {$bde?->last_name} to address {$gr->reports_count} grouped report(s) with priority {$gr->priority}.\n\n";
        $lines .= "ISSUE DESCRIPTION\n{$gr->message}\n\n";
        $lines .= "INDIVIDUAL REPORTS\n";

        foreach ($reports as $i => $r) {
            $lines .= ($i + 1) . ". [{$r->category?->name}] {$r->student?->first_name} {$r->student?->last_name}: {$r->description}\n";
        }

        $lines .= "\nRECOMMENDED ACTIONS\nReview the reports listed above during the scheduled meeting and coordinate with the relevant departments to resolve the identified issues.";

        return $lines;
    }

    private function buildHtml(Meeting $meeting, RequestMeeting $requestMeeting, string $aiContent): string
    {
        $gr      = $requestMeeting->generatedReport;
        $reports = $gr->reports ?? collect();
        $bde     = $requestMeeting->bde;

        $priorityColors = ['P0' => '#f43f5e', 'P1' => '#f59e0b', 'P2' => '#06b6d4', 'P3' => '#7c3aed'];
        $pColor = $priorityColors[strtoupper($gr->priority ?? 'P2')] ?? '#7c3aed';

        $rows = '';
        foreach ($reports as $r) {
            $rows .= '<tr>
                <td>' . e($r->category?->name ?? 'N/A') . '</td>
                <td>' . e(($r->student?->first_name ?? '') . ' ' . ($r->student?->last_name ?? '')) . '</td>
                <td>' . e($r->created_at?->format('M j, Y') ?? 'N/A') . '</td>
                <td>' . nl2br(e($r->description ?? 'No description')) . '</td>
            </tr>';
        }

        $noRows = $rows ? '' : '<tr><td colspan="4" style="text-align:center;color:#6b7280;padding:2rem;">No individual reports</td></tr>';

        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Meeting Report — ' . e($meeting->title) . '</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: "Segoe UI", Arial, sans-serif; font-size: 12px; color: #111; background: #fff; }
  .page { max-width: 900px; margin: 0 auto; padding: 0; }

  /* Header */
  .header { background: linear-gradient(135deg, #0f0f0f 0%, #1a1a2e 100%); color: white; padding: 40px 36px; position: relative; overflow: hidden; }
  .header::before { content: ""; position: absolute; top: -60px; right: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(124,58,237,0.4) 0%, transparent 70%); border-radius: 50%; }
  .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .brand { font-size: 11px; font-weight: 700; letter-spacing: 0.2em; color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 12px; }
  .header h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 6px; }
  .header .subtitle { font-size: 13px; color: rgba(255,255,255,0.6); }
  .priority-chip { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; }
  .priority-dot { width: 8px; height: 8px; border-radius: 50%; background: ' . $pColor . '; box-shadow: 0 0 8px ' . $pColor . '; }

  /* Meta bar */
  .meta-bar { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 16px 36px; display: flex; gap: 32px; }
  .meta-item label { display: block; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; margin-bottom: 3px; }
  .meta-item p { font-size: 12px; font-weight: 600; color: #111; }
  .meta-item a { color: #7c3aed; text-decoration: none; }

  /* Content */
  .content { padding: 32px 36px; }
  .section { margin-bottom: 28px; }
  .section-title { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #7c3aed; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #ede9fe; }

  /* Info grid */
  .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
  .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
  .info-box label { display: block; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 4px; }
  .info-box p { font-size: 12px; color: #111; font-weight: 500; }

  /* AI content */
  .ai-box { background: #fafafa; border: 1px solid #e5e7eb; border-left: 3px solid #7c3aed; border-radius: 0 8px 8px 0; padding: 16px 18px; white-space: pre-wrap; line-height: 1.8; font-size: 12px; color: #374151; }

  /* Table */
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead tr { background: #111; color: white; }
  th { padding: 10px 12px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
  td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: top; color: #374151; }
  tr:last-child td { border-bottom: none; }
  tr:nth-child(even) td { background: #fafafa; }

  /* Footer */
  .footer { margin-top: 32px; padding: 20px 36px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; color: #9ca3af; font-size: 10px; }
  .footer strong { color: #374151; }

  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { margin: 10mm; }
  }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="brand">YouPorts — Meeting Report</div>
    <div class="header-top">
      <div>
        <h1>' . e($meeting->title) . '</h1>
        <p class="subtitle">Meeting #' . $meeting->id . ' &nbsp;·&nbsp; Generated ' . now()->format('F j, Y') . '</p>
      </div>
      <div class="priority-chip">
        <span class="priority-dot"></span>
        ' . strtoupper($gr->priority ?? 'P2') . ' Priority
      </div>
    </div>
  </div>

  <div class="meta-bar">
    <div class="meta-item">
      <label>Date &amp; Time</label>
      <p>' . e($meeting->date?->format('l, F j, Y · g:i A') ?? 'N/A') . '</p>
    </div>
    <div class="meta-item">
      <label>Google Meet</label>
      <p><a href="' . e($meeting->link ?? '#') . '">' . e($meeting->link ?? 'N/A') . '</a></p>
    </div>
    <div class="meta-item">
      <label>BDE Contact</label>
      <p>' . e(($bde?->first_name ?? '') . ' ' . ($bde?->last_name ?? '')) . '</p>
    </div>
    <div class="meta-item">
      <label>Reports Grouped</label>
      <p>' . ($gr->reports_count ?? 0) . ' report(s)</p>
    </div>
  </div>

  <div class="content">

    <div class="section">
      <div class="section-title">Meeting Details</div>
      <div class="info-grid">
        <div class="info-box">
          <label>BDE Email</label>
          <p>' . e($bde?->email ?? 'N/A') . '</p>
        </div>
        <div class="info-box">
          <label>Proposed Date (BDE)</label>
          <p>' . e($requestMeeting->meeting_date?->format('M j, Y g:i A') ?? 'N/A') . '</p>
        </div>
        <div class="info-box">
          <label>BDE Notes</label>
          <p>' . e($requestMeeting->notes ?? 'No notes provided') . '</p>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-title">AI-Generated Summary</div>
      <div class="ai-box">' . e($aiContent) . '</div>
    </div>

    <div class="section">
      <div class="section-title">Individual Reports (' . $reports->count() . ')</div>
      <table>
        <thead>
          <tr>
            <th style="width:16%">Category</th>
            <th style="width:20%">Student</th>
            <th style="width:12%">Date</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>' . $rows . $noRows . '</tbody>
      </table>
    </div>

  </div>

  <div class="footer">
    <span><strong>YOUPORTS</strong> — Campus Issue Management System</span>
    <span>Auto-generated · ' . now()->format('F j, Y g:i A') . ' · <em>Print → Save as PDF</em></span>
  </div>

</div>
</body>
</html>';
    }
}
