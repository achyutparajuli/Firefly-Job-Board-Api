<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME','Job Listing Project') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 800px;
            width: 100%;
            margin-bottom: 20px;
        }

        .container h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 1.1em;
            margin-bottom: 30px;
            line-height: 1.6;
            color: #666;
        }

        .button {
            background-color: #007bff;
            color: #fff;
            padding: 15px 30px;
            margin-top: 1%;
            margin-left: 1%;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .features,
        .testimonials {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 20px 0;
        }

        .feature,
        .testimonial {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            flex: 1 1 45%;
            margin: 10px;
            text-align: left;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #333;
            color: #fff;
            width: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Job Listing Project</h1>
        <p>
            Welcome to our Job Listing Project! This platform allows you to search for jobs, apply for positions,
            and manage your job applications with ease. Whether you are looking for your first job or a career change,
            our app provides a wide range of listings to help you find the perfect opportunity.
        </p>
        <a href="#" class="button" target="_blank">Get the App on Play Store</a>
        <a href="#" class="button" target="_blank">Get the App on App Store</a>
    </div>

    <div class="container">
        <h2>Features</h2>
        <div class="features">
            @foreach($features as $feature)
            <div class="feature">
                <h3>{{ $feature['heading'] }}</h3>
                <p>{{ $feature['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="footer">
        &copy; 2024 {{ env('APP_NAME', 'Job Listing Project') }}. All rights reserved.
    </div>
</body>

</html>