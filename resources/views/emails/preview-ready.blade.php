<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <title>תצוגה מקדימה מוכנה</title>
</head>
<body style="font-family: Arial, sans-serif; direction: rtl; text-align: right;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #ec4899;">🎉 התצוגה המקדימה שלך מוכנה!</h1>
        <p>שלום {{ $coupleName }},</p>
        <p>העמוד שלך מוכן! תוכל לצפות ולערוך אותו לפני הרכישה.</p>
        <a href="{{ $previewUrl }}" style="display: inline-block; background-color: #ec4899; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-top: 20px;">
            צפה בתצוגה המקדימה
        </a>
    </div>
</body>
</html>

