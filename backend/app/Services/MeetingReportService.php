<?php

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Model\Meeting;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeetingReportService
{
    public function generateReport(Meeting $meeting, RequestMeeting $requestMeeting): ?string
    {
        try {
            $requestMeeting->load(['bde', 'generatedReport.reports.student', 'generatedReport.reports.category']);
            
            $html = $this->buildHtmlReport($meeting, $requestMeeting);
            $filename = 'meeting-reports/meeting-' . $meeting->id . '-' . Str::random(8) . '.html';
            
            if (Storage::disk('public')->put($filename, $html)) {
                return $filename;
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to generate meeting report: ' . $e->getMessage());
            return null;
        }
    }
    
    private function buildHtmlReport(Meeting $meeting, RequestMeeting $requestMeeting): string
    {
        $bde = $requestMeeting->bde;
        $generatedReport = $requestMeeting->generatedReport;
        $reports = $generatedReport->reports ?? collect();
        
        $priorityColor = $this->getPriorityColor($generatedReport->priority ?? 'P2');
        
        $individualReportsHtml = '';
        foreach ($reports as $report) {
            $category = $report->category ? $report->category->name : 'Unknown';
            $student = $report->student ? $report->student->first_name . ' ' . $report->student->last_name : 'Unknown';
            $individualReportsHtml .= '
                <tr>
                    <td>' . e($category) . '</td>
                    <td>' . e($student) . '</td>
                    <td>' . $report->created_at->format('M j, Y') . '</td>
                    <td>' . e($report->message ?? 'No message') . '</td>
                </tr>';
        }
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meeting Report - ' . e($meeting->title) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #333; padding: 20px; }
        .header { background: linear-gradient(135deg, #6366f1, #ec4899); color: white; padding: 30px; text-align: center; margin: -20px -20px 30px -20px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { opacity: 0.9; }
        .section { margin-bottom: 25px; }
        .section h2 { font-size: 14px; color: #6366f1; border-bottom: 2px solid #6366f1; padding-bottom: 5px; margin-bottom: 15px; text-transform: uppercase; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .info-item { background: #f8fafc; padding: 10px; border-radius: 8px; }
        .info-item label { font-weight: bold; color: #64748b; font-size: 10px; text-transform: uppercase; }
        .info-item p { margin-top: 3px; }
        .priority-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; color: white; }
        .message-box { background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e293b; color: white; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:hover { background: #f8fafc; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 10px; }
        @media print { body { padding: 0; } .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>YOUPORTS Meeting Report</h1>
        <p>' . e($meeting->title) . '</p>
    </div>
    
    <div class="section">
        <h2>Meeting Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Meeting ID</label>
                <p>#' . $meeting->id . '</p>
            </div>
            <div class="info-item">
                <label>Date & Time</label>
                <p>' . ($meeting->date ? $meeting->date->format('l, F j, Y \a\t g:i A') : 'N/A') . '</p>
            </div>
            <div class="info-item">
                <label>Google Meet Link</label>
                <p><a href="' . e($meeting->link) . '" style="color: #6366f1;">' . e($meeting->link) . '</a></p>
            </div>
            <div class="info-item">
                <label>Created</label>
                <p>' . $meeting->created_at->format('F j, Y g:i A') . '</p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>BDE Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Name</label>
                <p>' . ($bde ? e($bde->first_name . ' ' . $bde->last_name) : 'N/A') . '</p>
            </div>
            <div class="info-item">
                <label>Email</label>
                <p>' . ($bde ? e($bde->email) : 'N/A') . '</p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Problem Summary</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Reports Count</label>
                <p>' . ($generatedReport->reports_count ?? 0) . ' individual reports</p>
            </div>
            <div class="info-item">
                <label>Priority</label>
                <p><span class="priority-badge" style="background: ' . $priorityColor . ';">' . strtoupper($generatedReport->priority ?? 'N/A') . '</span></p>
            </div>
        </div>
        <div class="message-box">
            <strong>Description:</strong><br>
            ' . nl2br(e($generatedReport->message ?? 'No description available')) . '
        </div>
    </div>
    
    <div class="section">
        <h2>Individual Reports</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 20%;">Student</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 50%;">Message</th>
                </tr>
            </thead>
            <tbody>
                ' . ($individualReportsHtml ?: '<tr><td colspan="4" style="text-align: center; color: #94a3b8;">No individual reports</td></tr>') . '
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>Auto-generated by YOUPORTS on ' . now()->format('F j, Y g:i A') . '</p>
        <p>This report can be printed as PDF using your browser (Ctrl+P / Cmd+P)</p>
    </div>
</body>
</html>';
    }
    
    private function getPriorityColor($priority): string
    {
        return match(strtoupper($priority)) {
            'P0' => '#ef4444',
            'P1' => '#f59e0b',
            'P2' => '#3b82f6',
            default => '#6366f1'
        };
    }
}
