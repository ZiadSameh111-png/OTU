<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة طالب</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .certificate {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 5px solid #ddd;
            text-align: center;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
        .header {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .content {
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 50px;
            font-size: 14px;
            color: #666;
        }
        .signature {
            display: inline-block;
            margin: 30px 0;
            padding-top: 20px;
            border-top: 2px solid #333;
            font-weight: bold;
        }
        .serial {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <h1>شهادة رسمية</h1>
        </div>
        
        <div class="content">
            <p>تشهد مؤسسة التعليم الأهلي بأن الطالب/ة:</p>
            <h2>{{ $student->name }}</h2>
            <p>رقم الهوية: {{ $student->student_id ?? 'غير متوفر' }}</p>
            <p>{{ $request->details }}</p>
            <p>قد تم إصدار هذه الشهادة بناءً على طلب الطالب/ة بتاريخ {{ \Carbon\Carbon::parse($request->request_date)->format('Y-m-d') }}.</p>
        </div>
        
        <div class="footer">
            <div class="signature">
                توقيع المسؤول <br>
                مؤسسة التعليم الأهلي
            </div>
            
            <div class="serial">
                رقم الشهادة: {{ $request->id }}-{{ date('Ymd') }}<br>
                تاريخ الإصدار: {{ $date }}
            </div>
        </div>
    </div>
</body>
</html> 