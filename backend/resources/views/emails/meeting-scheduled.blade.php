<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meeting Scheduled - YOUPORTS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc; }
        .email-container { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0 0 10px 0; font-size: 28px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.95; font-size: 16px; }
        .content { padding: 30px; }
        .meeting-card { background: #f8fafc; border-radius: 12px; padding: 25px; margin-bottom: 25px; border-left: 4px solid #6366f1; }
        .meeting-card h2 { margin: 0 0 15px 0; font-size: 20px; color: #1e293b; }
        .meeting-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .info-item { background: white; padding: 12px 15px; border-radius: 8px; }
        .info-item label { display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .info-item p { margin: 0; font-size: 14px; color: #1e293b; }
        .btn-container { text-align: center; margin: 25px 0; }
        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white !important; padding: 16px 40px; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4); transition: transform 0.2s, box-shadow 0.2s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5); }
        .summary { background: #fef3c7; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .summary h3 { margin: 0 0 10px 0; color: #92400e; font-size: 14px; }
        .summary p { margin: 0; color: #78350f; font-size: 14px; }
        .instructions { background: #f0fdf4; border-radius: 12px; padding: 20px; margin-top: 20px; }
        .instructions h3 { margin: 0 0 10px 0; color: #166534; font-size: 14px; }
        .instructions ul { margin: 0; padding-left: 20px; color: #15803d; font-size: 13px; }
        .instructions li { margin-bottom: 5px; }
        .footer { text-align: center; padding: 25px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
        @media print { body { background: white; } .email-container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>YOUPORTS</h1>
            <p>Meeting Scheduled - {{ $meeting->title }}</p>
        </div>
        
        <div class="content">
            <p style="font-size: 16px; color: #1e293b;">Hello {{ $meeting->requestMeeting->bde->first_name ?? 'User' }},</p>
            <p style="color: #64748b;">Great news! Your meeting request has been approved. A meeting has been scheduled for you.</p>
            
            <div class="meeting-card">
                <h2>{{ $meeting->title }}</h2>
                
                <div class="meeting-info">
                    <div class="info-item">
                        <label>Date & Time</label>
                        <p>{{ \Carbon\Carbon::parse($meeting->date)->format('l, F j, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="info-item">
                        <label>Meeting Platform</label>
                        <p>Jitsi Meet</p>
                    </div>
                </div>
                
                @if($meeting->requestMeeting->notes)
                <div class="summary">
                    <h3>Notes from BDE</h3>
                    <p>{{ $meeting->requestMeeting->notes }}</p>
                </div>
                @endif
            </div>
            
            <div class="btn-container">
                <a href="{{ $meeting->link }}" class="btn">Join Meeting</a>
            </div>
            
            @if($meeting->requestMeeting->generatedReport)
            <div class="summary">
                <h3>Problem Summary</h3>
                <p><strong>{{ $meeting->requestMeeting->generatedReport->reports_count ?? 0 }} Reports</strong> - Priority: {{ strtoupper($meeting->requestMeeting->generatedReport->priority ?? 'N/A') }}</p>
                <p style="margin-top: 10px;">{{ $meeting->requestMeeting->generatedReport->message ?? 'No additional description' }}</p>
            </div>
            @endif
            
            <div class="instructions">
                <h3>Before Joining:</h3>
                <ul>
                    <li>Make sure your camera and microphone are working</li>
                    <li>Join a few minutes early to test your connection</li>
                    <li>Have your notes ready for the discussion</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>YOUPORTS</strong> - School Problem Reporting System</p>
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
