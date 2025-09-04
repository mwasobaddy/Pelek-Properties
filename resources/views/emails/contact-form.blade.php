<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            margin: 20px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #02c9c2, #012e2b);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .field {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #02c9c2;
        }
        .field-label {
            font-weight: 600;
            color: #012e2b;
            margin-bottom: 5px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field-value {
            color: #555;
            font-size: 16px;
            margin: 0;
        }
        .message-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #02c9c2;
            margin-top: 10px;
        }
        .message-content p {
            margin: 0;
            white-space: pre-wrap;
            color: #555;
            font-size: 16px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .service-badge {
            display: inline-block;
            background: linear-gradient(135deg, #02c9c2, #012e2b);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 New Contact Form Submission</h1>
            <p>A potential client has reached out to Pelek Properties</p>
        </div>

        <div class="field">
            <div class="field-label">👤 Name</div>
            <p class="field-value">{{ $formData['name'] }}</p>
        </div>

        <div class="field">
            <div class="field-label">📧 Email</div>
            <p class="field-value">{{ $formData['email'] }}</p>
        </div>

        <div class="field">
            <div class="field-label">📱 Phone</div>
            <p class="field-value">{{ $formData['phone'] }}</p>
        </div>

        <div class="field">
            <div class="field-label">🎯 Service Requested</div>
            <span class="service-badge">{{ $formData['services'][$formData['selectedService']] ?? $formData['selectedService'] }}</span>
        </div>

        <div class="field">
            <div class="field-label">📝 Subject</div>
            <p class="field-value">{{ $formData['subject'] }}</p>
        </div>

        <div class="field">
            <div class="field-label">💬 Message</div>
            <div class="message-content">
                <p>{{ $formData['message'] }}</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Pelek Properties</strong> - Professional Real Estate Services</p>
            <p>This message was sent from the contact form on your website</p>
            <p>Please respond to the client at your earliest convenience</p>
        </div>
    </div>
</body>
</html>
