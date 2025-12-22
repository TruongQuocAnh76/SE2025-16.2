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
        }

        .page img.bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* keeps aspect */
            z-index: 0;
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
            bottom: 54px
        }

        .badge {
            position: absolute;
            right: 70px;
            bottom: 80px;
            width: 160px
        }
    </style>
</head>

<body>
    <div class="page">
        <img class="bg" src="{{ public_path('cert_assets/certificate_bg.png') }}" alt="background">

        <div class="content">
            <div class="title">CERTIFICATE OF CERTCHAIN</div>
            <div class="subtitle">This certificate is awarded to</div>
            <div class="name">{{ $name }}</div>
            <div class="description">{{ $description }}</div>

            <div class="info-row">
                <div>Certificate NO: <strong>{{ $certificate_no }}</strong></div>
                <div>Issuing date: <strong>{{ $issuing_date }}</strong></div>
            </div>

            <div class="signature">
                <!-- <img src="{{ public_path('cert_assets/signature.png') }}" style="width:130px" alt="sign"> -->
                <div>Je-Yoon Shin</div>
                <div>Samsung chairman</div>
            </div>

            <div class="logo">
                <img src="{{ public_path('cert_assets/certchain_logo.svg') }}" width="160" alt="logo">
            </div>

            <img class="badge" src="{{ public_path('cert_assets/award_badge.png') }}" alt="badge">
        </div>
    </div>
</body>

</html>