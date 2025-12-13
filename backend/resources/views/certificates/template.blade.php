<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%
        }

        .page {
            position: relative;
            width: 1050px;
            height: 700px;
            font-family: "Times New Roman", serif;
            overflow: hidden;
            border: 3px solid #dee2e6;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .page::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 30px;
            right: 30px;
            bottom: 30px;
            border: 2px dashed #6c757d;
            border-radius: 5px;
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 40px 70px;
            color: #222;
        }

        .title {
            text-align: center;
            color: #2f2f99;
            font-size: 28px;
            letter-spacing: 2px;
            margin-top: 8px
        }

        .subtitle {
            text-align: center;
            font-size: 16px;
            margin-top: 28px
        }

        .name {
            text-align: center;
            font-size: 44px;
            font-weight: 700;
            margin: 8px 0
        }

        .description {
            text-align: center;
            font-size: 18px;
            margin-top: 14px;
            color: #444
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
            margin-top: 56px;
            font-size: 14px
        }

        .signature {
            position: absolute;
            left: 40px;
            bottom: 70px;
            font-size: 13px;
            text-align: left
        }

        .logo {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 54px;
            font-weight: bold;
            color: #2f2f99;
            font-size: 16px;
        }

        .badge {
            position: absolute;
            right: 70px;
            bottom: 80px;
            width: 80px;
            height: 80px;
            background: #ffd700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #b8860b;
            font-size: 12px;
            text-align: center;
            border: 3px solid #b8860b;
        }
    </style>
</head>

<body>
    <div class="page" style="background: url('{{ $bg_image }}') no-repeat center center; background-size: cover;">
        <div class="content">
            <div class="title">CERTIFICATE OF COMPLETION</div>
            <div class="subtitle">This certificate is awarded to</div>
            <div class="name">{{ $student }}</div>
            <div class="description">
                for successfully completing the course <strong>{{ $course }}</strong><br>
                with a final score of <strong>{{ $score }}%</strong>
            </div>

            <div class="info-row">
                <div>Certificate NO: <strong>{{ $certificate_number }}</strong></div>
                <div>Issued on: <strong>{{ $issue_date }}</strong></div>
            </div>

            <div class="signature">
                <img src="{{ $logo_image }}" alt="CertChain Logo" style="width: 101px; height: 41.5px;">
            </div>

            <div class="logo">
                CERTCHAIN<br>
                <span style="font-size: 12px;">Learning Platform</span>
            </div>

            <div class="badge">
                <img src="{{ $badge_image }}" alt="Award Badge" style="width: 80px; height: 80px;">
            </div>
        </div>
    </div>
</body>

</html>