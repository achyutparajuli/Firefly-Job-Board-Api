<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            color: #333;
        }

        .content {
            margin-bottom: 20px;
        }

        .content p {
            color: #666;
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .footer p {
            color: #666;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>User Verification Notification</h2>
        </div>
        <div class="content">
            <p>Dear
                <strong>{{ $name }}</strong>
            </p>

            <p>Thank you for connecting with us.</strong>.

                <br>Please click the link below to verify your email.
            </p>
            <div>
                <!-- Add more details here on what you want to show in the email -->
            </div>

            <p><a href="{{ url('/',['uuid' => $verifyToken]) }}" class="button" style="color: white;">Verify Email</a>
            </p>
        </div>
        <div class="footer">
            <p>Regards,<br>{{ env('APP_NAME') }}</p>
        </div>
    </div>
</body>

</html>