<?php

namespace App\Services;

use App\Model\Meeting;
use App\Model\RequestMeeting;
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
            $filename  = 'meeting-reports/meeting-' . $meeting->id . '-' . Str::random(8) . '.html';

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
                        ['role' => 'user',   'content' => $this->buildPrompt($meeting, $requestMeeting)],
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

        $reportLines = $reports->map(fn($r, $i) => sprintf(
            "Report %d: Category=%s | Student=%s %s | Description=%s",
            $i + 1,
            $r->category->name ?? 'Unknown',
            $r->student->first_name ?? '',
            $r->student->last_name ?? '',
            $r->description ?? 'N/A'
        ))->implode("\n");

        return "Generate a professional meeting summary for YOUPORTS school issue management.

MEETING: {$meeting->title}
DATE: {$meeting->date?->format('l, F j, Y g:i A')}
ROOM: {$meeting->link}
BDE: {$bde?->first_name} {$bde?->last_name} ({$bde?->email})
PRIORITY: {$gr->priority} | REPORTS: {$gr->reports_count}
DESCRIPTION: {$gr->message}

REPORTS:
{$reportLines}

Write sections: Executive Summary, Key Issues, Affected Students, Priority Assessment, Recommended Actions.";
    }

    private function fallbackContent(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        $gr      = $requestMeeting->generatedReport;
        $reports = $gr->reports ?? collect();
        $bde     = $requestMeeting->bde;

        $out  = "EXECUTIVE SUMMARY\n";
        $out .= "BDE member {$bde?->first_name} {$bde?->last_name} requested this meeting to address {$gr->reports_count} grouped report(s) with priority {$gr->priority}.\n\n";
        $out .= "ISSUE DESCRIPTION\n{$gr->message}\n\n";
        $out .= "INDIVIDUAL REPORTS\n";

        foreach ($reports as $i => $r) {
            $out .= ($i + 1) . ". [{$r->category?->name}] {$r->student?->first_name} {$r->student?->last_name}: {$r->description}\n";
        }

        $out .= "\nRECOMMENDED ACTIONS\nReview the reports above during the meeting and coordinate with relevant departments to resolve the identified issues.";

        return $out;
    }

    private function buildHtml(Meeting $meeting, RequestMeeting $requestMeeting, string $aiContent): string
    {
        $gr      = $requestMeeting->generatedReport;
        $reports = $gr->reports ?? collect();
        $bde     = $requestMeeting->bde;
        $pLabel  = strtoupper($gr->priority ?? 'P3');

        $pMap = [
            'P0' => ['color' => '#f43f5e', 'bg' => 'rgba(244,63,94,0.12)',  'border' => 'rgba(244,63,94,0.3)'],
            'P1' => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.12)', 'border' => 'rgba(245,158,11,0.3)'],
            'P2' => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.12)',  'border' => 'rgba(6,182,212,0.3)'],
            'P3' => ['color' => '#a78bfa', 'bg' => 'rgba(124,58,237,0.12)', 'border' => 'rgba(124,58,237,0.3)'],
        ];
        $p = $pMap[$pLabel] ?? $pMap['P3'];

        $rows = '';
        foreach ($reports as $i => $r) {
            $bg = $i % 2 === 0 ? '#0a0a0a' : '#111111';
            $rows .= '<tr style="background:' . $bg . ';">'
                . '<td style="padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04);color:#a78bfa;font-weight:600;font-size:11px;">' . e($r->category?->name ?? 'N/A') . '</td>'
                . '<td style="padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04);color:#e2e8f0;font-size:11px;">' . e(($r->student?->first_name ?? '') . ' ' . ($r->student?->last_name ?? '')) . '</td>'
                . '<td style="padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04);color:#4b5563;font-size:11px;">' . e($r->created_at?->format('M j, Y') ?? 'N/A') . '</td>'
                . '<td style="padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04);color:#e2e8f0;font-size:11px;line-height:1.6;">' . nl2br(e($r->description ?? 'No description')) . '</td>'
                . '</tr>';
        }

        $noRows = $rows ? '' : '<tr><td colspan="4" style="text-align:center;color:#4b5563;padding:2rem;font-size:12px;">No individual reports</td></tr>';

        $css = '
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  font-size: 12px; color: #ffffff; background: #000000;
  -webkit-font-smoothing: antialiased;
}
.page { max-width: 960px; margin: 0 auto; padding-bottom: 48px; }

/* Header */
.header {
  background: #000;
  padding: 40px 40px 32px;
  border-bottom: 1px solid rgba(124,58,237,0.2);
  position: relative; overflow: hidden;
}
.header-orb1 {
  position: absolute; top: -120px; right: -120px;
  width: 400px; height: 400px; border-radius: 50%;
  background: radial-gradient(circle, rgba(124,58,237,0.2) 0%, transparent 70%);
  pointer-events: none;
}
.header-orb2 {
  position: absolute; bottom: -80px; left: -80px;
  width: 300px; height: 300px; border-radius: 50%;
  background: radial-gradient(circle, rgba(6,182,212,0.12) 0%, transparent 70%);
  pointer-events: none;
}
.header-inner { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start; }
.brand { font-size: 9px; font-weight: 800; letter-spacing: 0.25em; text-transform: uppercase; color: rgba(167,139,250,0.5); margin-bottom: 10px; }
.header h1 {
  font-size: 26px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 6px;
  background: linear-gradient(135deg, #ffffff 0%, #a78bfa 60%, #06b6d4 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.header-sub { font-size: 12px; color: rgba(255,255,255,0.3); }
.priority-chip {
  display: inline-flex; align-items: center; gap: 7px;
  background: ' . $p['bg'] . '; border: 1px solid ' . $p['border'] . ';
  padding: 7px 14px; border-radius: 9999px;
  font-size: 10px; font-weight: 800; letter-spacing: 0.1em; color: ' . $p['color'] . '; flex-shrink: 0;
}
.priority-dot { width: 7px; height: 7px; border-radius: 50%; background: ' . $p['color'] . '; box-shadow: 0 0 8px ' . $p['color'] . '; }

/* Meta bar */
.meta-bar {
  background: #0a0a0a; border-bottom: 1px solid rgba(255,255,255,0.04);
  padding: 16px 40px; display: flex;
}
.meta-item { flex: 1; padding-right: 20px; margin-right: 20px; border-right: 1px solid rgba(255,255,255,0.04); }
.meta-item:last-child { border-right: none; margin-right: 0; padding-right: 0; }
.meta-label { display: block; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #4b5563; margin-bottom: 4px; }
.meta-value { font-size: 12px; font-weight: 600; color: #e2e8f0; }
.meta-value a { color: #06b6d4; text-decoration: none; }

/* Content */
.content { padding: 32px 40px; }
.section { margin-bottom: 28px; }
.section-title {
  font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #a78bfa;
  margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid rgba(124,58,237,0.2);
  display: flex; align-items: center; gap: 8px;
}
.section-title-bar { display: inline-block; width: 3px; height: 12px; background: linear-gradient(180deg, #a78bfa, #06b6d4); border-radius: 2px; }

/* Info grid */
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.info-box { background: #0a0a0a; border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 12px 14px; }
.info-box label { display: block; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #4b5563; margin-bottom: 5px; }
.info-box p { font-size: 12px; color: #e2e8f0; font-weight: 500; }

/* AI box */
.ai-box {
  background: #0a0a0a; border: 1px solid rgba(255,255,255,0.05);
  border-left: 3px solid #7c3aed; border-radius: 0 10px 10px 0;
  padding: 18px 20px; white-space: pre-wrap; line-height: 1.9; font-size: 12px; color: #e2e8f0;
}

/* Table */
.table-wrap { border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
thead tr { background: #111111; }
th { padding: 11px 14px; text-align: left; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #4b5563; }

/* Footer */
.footer {
  margin: 0 40px; padding: 18px 0 0;
  border-top: 1px solid rgba(255,255,255,0.04);
  display: flex; justify-content: space-between; align-items: center;
  color: #4b5563; font-size: 10px;
}
.footer-brand {
  font-weight: 800; letter-spacing: 0.08em;
  background: linear-gradient(135deg, #a78bfa, #06b6d4);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}

@media print {
  body { background: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  @page { margin: 8mm; background: #000; }
}';

        $genDate = now()->format('F j, Y · g:i A');
        $meetDate = e($meeting->date?->format('M j, Y · g:i A') ?? 'N/A');
        $proposedDate = e($requestMeeting->meeting_date?->format('M j, Y · g:i A') ?? 'N/A');

        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Meeting Report — ' . e($meeting->title) . '</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>' . $css . '</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="header-orb1"></div>
    <div class="header-orb2"></div>
    <div class="header-inner">
      <div>
        <div class="brand">YouPorts &nbsp;·&nbsp; Meeting Report</div>
        <h1>' . e($meeting->title) . '</h1>
        <p class="header-sub">Meeting #' . $meeting->id . ' &nbsp;·&nbsp; Generated ' . $genDate . '</p>
      </div>
      <div class="priority-chip">
        <span class="priority-dot"></span>
        ' . $pLabel . ' Priority
      </div>
    </div>
  </div>

  <div class="meta-bar">
    <div class="meta-item">
      <span class="meta-label">Date &amp; Time</span>
      <span class="meta-value">' . $meetDate . '</span>
    </div>
    <div class="meta-item">
      <span class="meta-label">Meeting Room</span>
      <span class="meta-value"><a href="' . e($meeting->link ?? '#') . '">' . e($meeting->link ?? 'N/A') . '</a></span>
    </div>
    <div class="meta-item">
      <span class="meta-label">BDE Contact</span>
      <span class="meta-value">' . e(($bde?->first_name ?? '') . ' ' . ($bde?->last_name ?? '')) . '</span>
    </div>
    <div class="meta-item">
      <span class="meta-label">Reports Grouped</span>
      <span class="meta-value">' . ($gr->reports_count ?? 0) . ' report(s)</span>
    </div>
  </div>

  <div class="content">

    <div class="section">
      <div class="section-title"><span class="section-title-bar"></span>Meeting Details</div>
      <div class="info-grid">
        <div class="info-box">
          <label>BDE Email</label>
          <p>' . e($bde?->email ?? 'N/A') . '</p>
        </div>
        <div class="info-box">
          <label>Proposed Date (BDE)</label>
          <p>' . $proposedDate . '</p>
        </div>
        <div class="info-box">
          <label>BDE Notes</label>
          <p>' . e($requestMeeting->notes ?? 'No notes provided') . '</p>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-title"><span class="section-title-bar"></span>AI-Generated Summary</div>
      <div class="ai-box">' . e($aiContent) . '</div>
    </div>

    <div class="section">
      <div class="section-title"><span class="section-title-bar"></span>Individual Reports (' . $reports->count() . ')</div>
      <div class="table-wrap">
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

  </div>

  <div class="footer">
    <span class="footer-brand">YOUPORTS</span>
    <span>' . $genDate . ' &nbsp;·&nbsp; Print &rarr; Save as PDF</span>
  </div>

</div>
</body>
</html>';
    }
}
