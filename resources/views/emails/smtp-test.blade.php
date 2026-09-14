Bu, {{ config('app.name') }} SMTP ayarlarının yoxlanması üçün göndərilən test məktubudur.

Göndərilmə vaxtı: {{ $sentAt }}
Mailer: {{ $mailer }}{{ $mailer === 'smtp' ? " / {$host}:{$port}" : '' }}

Bu məktubu aldınızsa, e-poçt göndərişi düzgün işləyir.
