<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificate->title ?? 'Certificate of Completion' }}</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 40px;
            background: #ffffff;
            color: #1a1a2e;
        }
        .certificate-wrapper {
            border: 3px solid #00d4ff;
            padding: 60px;
            text-align: center;
            min-height: 500px;
            position: relative;
        }
        .certificate-badge {
            font-size: 48px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 36px;
            color: #0f0f23;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        h2 {
            font-size: 24px;
            color: #9945ff;
            margin-bottom: 20px;
        }
        .awarded-to {
            font-size: 14px;
            color: #718096;
            margin-bottom: 10px;
        }
        .recipient-name {
            font-size: 42px;
            font-weight: bold;
            color: #0f0f23;
            margin-bottom: 10px;
            border-bottom: 2px solid #00d4ff;
            display: inline-block;
            padding-bottom: 10px;
        }
        .completion-text {
            font-size: 18px;
            color: #4a5568;
            margin: 20px 0;
        }
        .course-title {
            font-size: 28px;
            font-weight: 600;
            color: #00d4ff;
            margin-bottom: 30px;
        }
        .certificate-details {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
            font-size: 14px;
            color: #718096;
        }
        .certificate-details span {
            display: block;
        }
        .certificate-number {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 40px;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .verify-url {
            color: #00d4ff;
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-badge">🏆</div>
        <h1>Certificate of Completion</h1>
        <h2>CyberLab Academy</h2>

        <div class="awarded-to">This certifies that</div>
        <div class="recipient-name">{{ $user->name ?? 'Student' }}</div>

        <div class="completion-text">Has successfully completed</div>
        <div class="course-title">
            {{ $course->title ?? ($lab->title ?? 'Course') }}
        </div>

        <div class="certificate-details">
            <span>Date: {{ $certificate->issued_at?->format('F d, Y') ?? now()->format('F d, Y') }}</span>
            <span>Certificate #: {{ $certificate->certificate_number ?? 'N/A' }}</span>
        </div>

        <div class="certificate-number">
            Verify this certificate at: <span class="verify-url">{{ $certificate->verification_url ?? 'https://cyberlab.academy/verify' }}</span>
        </div>

        <div class="footer">
            <p>CyberLab Academy - Master cybersecurity through hands-on practice.</p>
            <p>© {{ now()->year }} CyberLab Academy. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
