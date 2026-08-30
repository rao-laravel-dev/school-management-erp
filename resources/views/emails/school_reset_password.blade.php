<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        
        /* Header with Image & Icon */
        .header { 
            background: #4e73df url('https://www.transparenttextures.com/patterns/cubes.png'); 
            padding: 40px 20px; 
            text-align: center; 
            color: white; 
        }
        .icon-circle {
            background: rgba(255,255,255,0.2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .content { padding: 40px; text-align: center; }
        
        /* Stylish Button */
        .btn {
            background: #4e73df;
            color: #ffffff !important;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin: 25px 0;
            box-shadow: 0 10px 20px rgba(78,115,223,0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer { background: #f8f9fc; padding: 30px; text-align: center; color: #858796; font-size: 13px; }
        .divider { height: 1px; background: #e3e6f0; margin: 25px 0; }
        
        /* Lock Icon Image Placeholder */
        .lock-img { width: 50px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="icon-circle">
                <!-- Lock Icon from an online source -->
                <img src="https://cdn-icons-png.flaticon.com/512/6195/6195700.png" alt="Lock" style="width: 45px; filter: brightness(0) invert(1);">
            </div>
            <h1 style="margin: 0; font-size: 28px; letter-spacing: 2px;">SMART SCHOOL</h1>
            <p style="opacity: 0.8; margin-top: 5px;">Secure Password Recovery</p>
        </div>

        <!-- Body -->
        <div class="content">
            <!-- Reset Illustration -->
            <img src="https://cdn-icons-png.flaticon.com/512/6357/6357042.png" alt="Reset" style="width: 100px; margin-bottom: 20px;">
            
            <h2 style="color: #2e59d9;">Reset Your Password?</h2>
            <p style="color: #5a5c69; font-size: 15px;">Assalam-o-Alaikum! Humein aapke account ke liye password reset ki request mili hai. Naya password set karne ke liye niche button click karein.</p>
            
            <a href="{{ url('reset-password/'.$token.'?email='.$email) }}" class="btn">
                Reset Password
            </a>

            <div class="divider"></div>
            
            <p style="color: #e74a3b; font-size: 13px; font-weight: 600;">
                <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png" width="15" style="vertical-align: middle;">
                Ye link 60 minutes mein expire ho jayega.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Agar aapne ye request nahi ki, toh is email ko ignore karein.</p>
            <p style="margin-top: 10px; font-weight: bold;">SmartSchool IT Support Team</p>
            <div style="margin-top: 15px;">
                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" width="20" style="margin: 0 5px;">
                <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" width="20" style="margin: 0 5px;">
            </div>
        </div>
    </div>
</body>
</html>